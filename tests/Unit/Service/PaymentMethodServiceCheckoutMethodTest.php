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

namespace Mollie\Tests\Unit\Service;

use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Mollie\Service\PaymentMethodService;
use Mollie\Tests\Unit\BaseTestCase;

class PaymentMethodServiceCheckoutMethodTest extends BaseTestCase
{
    /**
     * @dataProvider checkoutMethodProvider
     */
    public function testItResolvesTheMethodChosenAtCheckout(?array $metadata, ?string $wallet, string $expected): void
    {
        $payment = new Payment($this->mock(MollieApiClient::class));
        $payment->id = 'tr_test';
        $payment->resource = 'payment';
        $payment->method = 'creditcard';
        $payment->metadata = $metadata === null ? null : (object) $metadata;
        $payment->details = $wallet === null ? null : (object) ['wallet' => $wallet];

        $this->assertSame($expected, $this->createService()->getCheckoutMethodId($payment));
    }

    public function checkoutMethodProvider(): array
    {
        return [
            'card chosen, settled through Apple Pay on the hosted page' => [
                ['cart_id' => 1, 'method_id' => 'creditcard'], 'applepay', 'creditcard',
            ],
            'Apple Pay Direct' => [
                ['cart_id' => 1, 'method_id' => 'applepay'], 'applepay', 'applepay',
            ],
            'plain card payment' => [
                ['cart_id' => 1, 'method_id' => 'creditcard'], null, 'creditcard',
            ],
            'payment created before the method was stored, wallet wins' => [
                ['cart_id' => 1], 'applepay', 'applepay',
            ],
            'payment created before the method was stored, no wallet' => [
                ['cart_id' => 1], null, 'creditcard',
            ],
            'payment without metadata' => [
                null, null, 'creditcard',
            ],
        ];
    }

    private function createService(): PaymentMethodService
    {
        return (new \ReflectionClass(PaymentMethodService::class))->newInstanceWithoutConstructor();
    }
}
