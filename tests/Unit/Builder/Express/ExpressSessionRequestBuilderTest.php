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
use Mollie\Exception\MollieException;
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

    public function testPaymentModeAsksForNoCustomerDetailsAndNoShippingCallback(): void
    {
        $this->cartLinesService->method('getCartLines')->willReturn([]);

        $body = $this->builder->build($this->cart, false)->jsonSerialize();

        $this->assertSame([], $body['requiredCustomerDetails']);
        $this->assertArrayNotHasKey('shipping', $body);
        $this->assertArrayNotHasKey(Config::EXPRESS_COLLECT_METADATA_FLAG, $body['metadata']);
        $this->assertSame(59.9, $this->builder->getSessionAmount($this->cart, false));
    }

    public function testCollectModeAsksMollieForTheCustomerAndTheShippingOption(): void
    {
        $cart = $this->collectCart(false);
        $this->cartLinesService->method('getCartLines')->willReturn([$this->line('physical', '54.90')]);

        $body = $this->builder->build($cart, true)->jsonSerialize();

        $this->assertSame(['currency' => 'EUR', 'value' => '54.90'], $body['amount']);
        $this->assertSame(['email', 'billing-address', 'shipping-address'], $body['requiredCustomerDetails']);
        $this->assertSame(1, $body['metadata'][Config::EXPRESS_METADATA_FLAG]);
        $this->assertSame(1, $body['metadata'][Config::EXPRESS_COLLECT_METADATA_FLAG]);
        $this->assertSame(7, $body['metadata']['cart_id']);
        $this->assertNotContains('shipping_fee', array_column($body['lines'], 'type'));

        $callbackUrl = $body['shipping']['callbackUrl'];
        parse_str((string) parse_url($callbackUrl, PHP_URL_QUERY), $callbackQuery);
        $this->assertStringContainsString('/mollie/' . ExpressSessionRequestBuilder::SHIPPING_CONTROLLER . '?', $callbackUrl);
        $this->assertSame('7', $callbackQuery['cart_id']);
        $this->assertSame(HashUtility::hash('cart-secure-key' . 'shipping'), $callbackQuery['token']);
        $this->assertSame($callbackQuery['token'], $this->builder->getShippingCallbackToken($cart));
    }

    public function testCollectModeBuildsLinesFromTheTotalAndSummaryWithoutShipping(): void
    {
        $cart = $this->collectCart(false);
        $this->cartLinesService
            ->expects($this->once())
            ->method('getCartLines')
            ->with(
                54.9,
                $this->anything(),
                'EUR',
                $this->callback(static function (array $summary) {
                    return 0 === $summary['total_shipping']
                        && 0 === $summary['total_shipping_tax_exc']
                        && 0 === $summary['total_wrapping'];
                }),
                [],
                true,
                'meal',
                LineType::PAYMENT
            )
            ->willReturn([$this->line('physical', '54.90')]);

        $this->builder->build($cart, true);
    }

    public function testCollectModeForAVirtualCartAsksForNoShippingAddress(): void
    {
        $cart = $this->collectCart(true);
        $this->cartLinesService->method('getCartLines')->willReturn([$this->line('digital', '54.90')]);

        $body = $this->builder->build($cart, true)->jsonSerialize();

        $this->assertSame(['email', 'billing-address'], $body['requiredCustomerDetails']);
        $this->assertArrayNotHasKey('shipping', $body);
    }

    /**
     * @dataProvider inconsistentCollectLinesProvider
     */
    public function testCollectModeRefusesLinesThatDoNotMatchTheAmount(array $lines): void
    {
        $cart = $this->collectCart(false);
        $this->cartLinesService->method('getCartLines')->willReturn($lines);

        $this->expectException(MollieException::class);

        $this->builder->build($cart, true);
    }

    public function inconsistentCollectLinesProvider(): array
    {
        return [
            'lines short of the amount' => [[$this->line('physical', '50.00')]],
            'shipping fee line left in' => [[$this->line('physical', '49.90'), $this->line('shipping_fee', '5.00')]],
        ];
    }

    /**
     * @return \Cart|\PHPUnit\Framework\MockObject\MockObject
     */
    private function collectCart(bool $isVirtual)
    {
        $cart = $this->mock(\Cart::class);
        $cart->id = 7;
        $cart->id_customer = 3;
        $cart->secure_key = 'cart-secure-key';
        $cart->method('getOrderTotal')->willReturnCallback(static function ($withTaxes, $type) {
            return \Cart::BOTH_WITHOUT_SHIPPING === $type ? 54.9 : 59.9;
        });
        $cart->method('getSummaryDetails')->willReturn([
            'total_wrapping' => 0,
            'total_shipping' => 5.0,
            'total_shipping_tax_exc' => 4.13,
        ]);
        $cart->method('getProducts')->willReturn([]);
        $cart->method('isVirtualCart')->willReturn($isVirtual);

        return $cart;
    }

    private function line(string $type, string $total): array
    {
        return [
            'description' => 'Line ' . $type,
            'quantity' => 1,
            'type' => $type,
            'unitPrice' => ['currency' => 'EUR', 'value' => $total],
            'totalAmount' => ['currency' => 'EUR', 'value' => $total],
            'vatAmount' => ['currency' => 'EUR', 'value' => '0.00'],
        ];
    }
}
