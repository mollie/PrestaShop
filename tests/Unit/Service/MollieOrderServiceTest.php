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

use Mollie\Api\Endpoints\PaymentEndpoint;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Mollie\Service\MollieOrderService;
use Mollie\Tests\Unit\BaseTestCase;

class MollieOrderServiceTest extends BaseTestCase
{
    /** @var MollieApiClient|\PHPUnit\Framework\MockObject\MockObject */
    private $apiClient;

    /** @var MollieOrderService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = $this->mock(MollieApiClient::class);
        $module = $this->mock(\Mollie::class);
        $module->method('getApiClient')->willReturn($this->apiClient);

        $this->service = new MollieOrderService($module);
    }

    /**
     * @dataProvider authorizedPaymentProvider
     */
    public function testAuthorizedPaymentCanRefundOnlyWhatWasCaptured(array $amounts, float $expected)
    {
        $fields = ['status' => 'authorized', 'amount' => $this->money('22.94')];
        foreach ($amounts as $name => $value) {
            $fields[$name] = $this->money($value);
        }
        $this->mockPayment($fields);

        $this->assertSame($expected, $this->service->getRefundableAmount('tr_test'));
    }

    public function authorizedPaymentProvider()
    {
        return [
            'nothing captured' => [['amountCaptured' => '0.00'], 0.0],
            'capture fields left out' => [[], 0.0],
            'partly captured and refunded' => [['amountCaptured' => '10.00', 'amountRefunded' => '4.00'], 6.0],
        ];
    }

    public function testPaidPaymentUsesRemainingAmountFromMollie()
    {
        $this->mockPayment([
            'status' => 'paid',
            'amount' => $this->money('22.94'),
            'amountRemaining' => $this->money('17.94'),
        ]);

        $this->assertSame(17.94, $this->service->getRefundableAmount('tr_test'));
    }

    private function mockPayment(array $fields)
    {
        $payment = new Payment($this->apiClient);
        $payment->id = 'tr_test';
        foreach ($fields as $name => $value) {
            $payment->$name = $value;
        }

        $endpoint = $this->mock(PaymentEndpoint::class);
        $endpoint->method('get')->willReturn($payment);
        $this->apiClient->payments = $endpoint;
    }

    private function money(string $value)
    {
        return (object) ['value' => $value, 'currency' => 'EUR'];
    }
}
