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

    public function testAuthorizedPaymentWithNothingCapturedHasNothingToRefund()
    {
        $this->mockPayment([
            'status' => 'authorized',
            'amount' => $this->money('22.94'),
            'amountCaptured' => $this->money('0.00'),
        ]);

        $this->assertSame(0.0, $this->service->getRefundableAmount('tr_test'));
    }

    public function testAuthorizedPaymentWithoutCaptureFieldsHasNothingToRefund()
    {
        $this->mockPayment([
            'status' => 'authorized',
            'amount' => $this->money('22.94'),
        ]);

        $this->assertSame(0.0, $this->service->getRefundableAmount('tr_test'));
    }

    public function testAuthorizedPaymentCanRefundWhatWasCapturedMinusRefunds()
    {
        $this->mockPayment([
            'status' => 'authorized',
            'amount' => $this->money('22.94'),
            'amountCaptured' => $this->money('10.00'),
            'amountRefunded' => $this->money('4.00'),
        ]);

        $this->assertSame(6.0, $this->service->getRefundableAmount('tr_test'));
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
