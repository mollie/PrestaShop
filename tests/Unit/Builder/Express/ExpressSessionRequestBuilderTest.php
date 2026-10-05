<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Builder\Express;

use Mollie\Builder\Express\ExpressSessionRequestBuilder;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Enum\LineType;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\CartLinesService;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\HashUtility;
use Mollie\Utility\SecureKeyUtility;

class ExpressSessionRequestBuilderTest extends BaseTestCase
{
    /** @var CartLinesService|\PHPUnit\Framework\MockObject\MockObject */
    private $cartLinesService;
    /** @var ModuleFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $moduleFactory;
    /** @var ExpressSessionRequestBuilder */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cartLinesService = $this->mock(CartLinesService::class);
        $this->moduleFactory = $this->mock(ModuleFactory::class);
        $this->moduleFactory->method('getModuleName')->willReturn('mollie');

        $this->context->method('getCurrencyIso')->willReturn('eur');
        $this->context->method('getModuleLink')->willReturnCallback(
            static function ($module, $controller, array $params) {
                return 'https://shop.test/module/' . $module . '/' . $controller . '?' . http_build_query($params);
            }
        );

        $this->configuration->method('get')->willReturnMap([
            ['PS_GIFT_WRAPPING', null, null, null, '1'],
            [Config::MOLLIE_VOUCHER_CATEGORY, null, null, null, 'meal'],
        ]);

        $this->cart->id = 7;
        $this->cart->id_customer = 3;
        $this->cart->secure_key = 'cart-secure-key';
        $this->cart->method('getOrderTotal')->willReturn(59.9);
        $this->cart->method('getSummaryDetails')->willReturn(['total_wrapping' => 0]);
        $this->cart->method('getTotalShippingCost')->willReturn(4.99);
        $this->cart->method('getProducts')->willReturn([]);

        $this->builder = new ExpressSessionRequestBuilder(
            $this->cartLinesService,
            $this->context,
            $this->configuration,
            $this->moduleFactory
        );
    }

    public function testItMirrorsThePaymentMetadataAndWebhookContract(): void
    {
        $this->cartLinesService->method('getCartLines')->willReturn([]);

        $body = $this->builder->build($this->cart)->jsonSerialize();

        $expectedKey = SecureKeyUtility::generateReturnKey(3, 7, 'mollie');

        $this->assertSame(['currency' => 'EUR', 'value' => '59.90'], $body['amount']);
        $this->assertStringStartsWith('mol_', $body['description']);
        $this->assertSame(7, $body['metadata']['cart_id']);
        $this->assertSame($body['description'], $body['metadata']['order_reference']);
        $this->assertSame($expectedKey, $body['metadata']['secure_key']);
        $this->assertSame(1, $body['metadata'][Config::EXPRESS_METADATA_FLAG]);
        $this->assertSame([], $body['requiredCustomerDetails']);

        parse_str((string) parse_url($body['payment']['webhookUrl'], PHP_URL_QUERY), $webhookQuery);
        $this->assertStringContainsString('/mollie/webhook?', $body['payment']['webhookUrl']);
        $this->assertSame(HashUtility::hash('cart-secure-key'), $webhookQuery['security_token']);

        parse_str((string) parse_url($body['redirectUrl'], PHP_URL_QUERY), $redirectQuery);
        $this->assertStringContainsString('/mollie/' . ExpressSessionRequestBuilder::RETURN_CONTROLLER . '?', $body['redirectUrl']);
        $this->assertSame('7', $redirectQuery['cart_id']);
        $this->assertSame($expectedKey, $redirectQuery['key']);
        $this->assertSame('1', $redirectQuery['utm_nooverride']);
    }

    public function testItBuildsLinesWithoutAFeeAndForThePaymentsApiShape(): void
    {
        $this->cartLinesService
            ->expects($this->once())
            ->method('getCartLines')
            ->with(
                59.9,
                $this->callback(static function (PaymentFeeData $fee) {
                    return !$fee->isActive() && 0.0 === $fee->getPaymentFeeTaxIncl();
                }),
                'EUR',
                ['total_wrapping' => 0],
                4.99,
                [],
                true,
                'meal',
                LineType::PAYMENT
            )
            ->willReturn([]);

        $this->builder->build($this->cart);
    }

    public function testItStripsFieldsTheSessionsApiRejects(): void
    {
        $line = new class() implements \JsonSerializable {
            public function jsonSerialize(): array
            {
                return [
                    'sku' => '',
                    'description' => 'T-shirt',
                    'productUrl' => null,
                    'imageUrl' => 'https://shop.test/img.png',
                    'quantity' => 1,
                    'vatRate' => '21.00',
                    'categories' => ['meal'],
                    'unitPrice' => ['currency' => 'EUR', 'value' => '10.00'],
                    'totalAmount' => ['currency' => 'EUR', 'value' => '10.00'],
                    'vatAmount' => ['currency' => 'EUR', 'value' => '1.74'],
                    'type' => 'physical',
                ];
            }
        };

        $lines = $this->builder->sanitizeLines([$line, ['description' => 'Shipping', 'categories' => [], 'type' => 'shipping_fee']]);

        $this->assertCount(2, $lines);
        $this->assertArrayNotHasKey('categories', $lines[0]);
        $this->assertArrayNotHasKey('productUrl', $lines[0]);
        $this->assertArrayNotHasKey('sku', $lines[0]);
        $this->assertSame('https://shop.test/img.png', $lines[0]['imageUrl']);
        $this->assertSame('physical', $lines[0]['type']);
        $this->assertSame(['description' => 'Shipping', 'type' => 'shipping_fee'], $lines[1]);
    }
}
