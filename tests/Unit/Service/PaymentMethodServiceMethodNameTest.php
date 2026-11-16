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
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\PaymentMethodService;
use Mollie\Tests\Unit\BaseTestCase;
use MolPaymentMethod;

class PaymentMethodServiceMethodNameTest extends BaseTestCase
{
    /**
     * @dataProvider methodlessPaymentProvider
     */
    public function testItLabelsAPaymentWithoutAMethod(?array $metadata, ?string $wallet, string $expected): void
    {
        $payment = new Payment($this->mock(MollieApiClient::class));
        $payment->id = 'tr_test';
        $payment->resource = 'payment';
        $payment->method = null;
        $payment->metadata = $metadata === null ? null : (object) $metadata;
        $payment->details = $wallet === null ? null : (object) ['wallet' => $wallet];

        $this->assertSame($expected, $this->createService()->getPaymentMethodName(new MolPaymentMethod(), $payment));
    }

    public function methodlessPaymentProvider(): array
    {
        return [
            'no method chosen yet, no stored method' => [
                ['cart_id' => 1], null, 'Mollie',
            ],
            'no metadata at all' => [
                null, null, 'Mollie',
            ],
            'method chosen at checkout is kept in the metadata' => [
                ['cart_id' => 1, 'method_id' => 'banktransfer'], null, 'Bank',
            ],
            'wallet reported without a method' => [
                ['cart_id' => 1], 'applepay', 'Apple Pay',
            ],
        ];
    }

    private function createService(): PaymentMethodService
    {
        $repository = $this->mock(PaymentMethodRepositoryInterface::class);
        $repository->method('getPaymentMethodIdByMethodId')->willReturn(false);

        $module = $this->mock(\Mollie::class);
        $module->displayName = 'Mollie';

        $reflection = new \ReflectionClass(PaymentMethodService::class);
        $service = $reflection->newInstanceWithoutConstructor();

        foreach (['methodRepository' => $repository, 'module' => $module] as $property => $value) {
            $reflectionProperty = $reflection->getProperty($property);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($service, $value);
        }

        return $service;
    }
}
