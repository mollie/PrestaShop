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

use Mollie\Api\Types\PaymentStatus;
use Mollie\Service\PayByBankCancellationService;
use PHPUnit\Framework\TestCase;

class PayByBankCancellationServiceTest extends TestCase
{
    /** @var PayByBankCancellationService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->getMockBuilder(PayByBankCancellationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActualMollieStatus', 'cancelOrderAndRestoreCart', 'findPendingPayByBankPayment'])
            ->getMock();
    }

    /**
     * @dataProvider shouldCancelPaymentDataProvider
     *
     * @param string $status
     * @param bool $expected
     */
    public function testShouldCancelPayment($status, $expected)
    {
        $service = $this->createRealServiceForPureMethods();

        self::assertEquals($expected, $service->shouldCancelPayment($status));
    }

    public function shouldCancelPaymentDataProvider()
    {
        return [
            'canceled status' => [
                'status' => PaymentStatus::STATUS_CANCELED,
                'expected' => true,
            ],
            'expired status' => [
                'status' => PaymentStatus::STATUS_EXPIRED,
                'expected' => true,
            ],
            'failed status' => [
                'status' => PaymentStatus::STATUS_FAILED,
                'expected' => true,
            ],
            'open status' => [
                'status' => PaymentStatus::STATUS_OPEN,
                'expected' => true,
            ],
            'pending status' => [
                'status' => PaymentStatus::STATUS_PENDING,
                'expected' => false,
            ],
            'paid status' => [
                'status' => PaymentStatus::STATUS_PAID,
                'expected' => false,
            ],
            'authorized status' => [
                'status' => PaymentStatus::STATUS_AUTHORIZED,
                'expected' => false,
            ],
            'empty string (API error)' => [
                'status' => '',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider resolveCancelStatusDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testResolveCancelStatus($input, $expected)
    {
        $service = $this->createRealServiceForPureMethods();

        self::assertEquals($expected, $service->resolveCancelStatus($input));
    }

    public function resolveCancelStatusDataProvider()
    {
        return [
            'open maps to canceled' => [
                'input' => PaymentStatus::STATUS_OPEN,
                'expected' => PaymentStatus::STATUS_CANCELED,
            ],
            'canceled passes through' => [
                'input' => PaymentStatus::STATUS_CANCELED,
                'expected' => PaymentStatus::STATUS_CANCELED,
            ],
            'expired passes through' => [
                'input' => PaymentStatus::STATUS_EXPIRED,
                'expected' => PaymentStatus::STATUS_EXPIRED,
            ],
            'failed passes through' => [
                'input' => PaymentStatus::STATUS_FAILED,
                'expected' => PaymentStatus::STATUS_FAILED,
            ],
        ];
    }

    /**
     * @dataProvider isTerminalFailureDataProvider
     *
     * @param string $status
     * @param bool $expected
     */
    public function testIsTerminalFailure($status, $expected)
    {
        $service = $this->createRealServiceForPureMethods();

        self::assertEquals($expected, $service->isTerminalFailure($status));
    }

    public function isTerminalFailureDataProvider()
    {
        return [
            'canceled' => [PaymentStatus::STATUS_CANCELED, true],
            'expired' => [PaymentStatus::STATUS_EXPIRED, true],
            'failed' => [PaymentStatus::STATUS_FAILED, true],
            'open' => [PaymentStatus::STATUS_OPEN, false],
            'pending' => [PaymentStatus::STATUS_PENDING, false],
            'paid' => [PaymentStatus::STATUS_PAID, false],
            'authorized' => [PaymentStatus::STATUS_AUTHORIZED, false],
            'empty' => ['', false],
        ];
    }

    public function testHandleAbandonedPaymentNoPendingPayment()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->with(42)
            ->willReturn(false);

        $this->service->expects($this->never())
            ->method('getActualMollieStatus');

        $this->service->expects($this->never())
            ->method('cancelOrderAndRestoreCart');

        $this->service->handleAbandonedPayment(42);
    }

    public function testHandleAbandonedPaymentTerminalFailureCancelsOrder()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->willReturn([
                'cart_id' => '100',
                'transaction_id' => 'tr_test123',
            ]);

        $this->service->method('getActualMollieStatus')
            ->with('tr_test123')
            ->willReturn(PaymentStatus::STATUS_CANCELED);

        $this->service->expects($this->once())
            ->method('cancelOrderAndRestoreCart')
            ->with(100, 'tr_test123', PaymentStatus::STATUS_CANCELED);

        $this->service->handleAbandonedPayment(42);
    }

    public function testHandleAbandonedPaymentOpenStatusCancelsWithCanceledStatus()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->willReturn([
                'cart_id' => '100',
                'transaction_id' => 'tr_test123',
            ]);

        $this->service->method('getActualMollieStatus')
            ->with('tr_test123')
            ->willReturn(PaymentStatus::STATUS_OPEN);

        $this->service->expects($this->once())
            ->method('cancelOrderAndRestoreCart')
            ->with(100, 'tr_test123', PaymentStatus::STATUS_CANCELED);

        $this->service->handleAbandonedPayment(42);
    }

    public function testHandleAbandonedPaymentPendingStatusDoesNotCancel()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->willReturn([
                'cart_id' => '100',
                'transaction_id' => 'tr_test123',
            ]);

        $this->service->method('getActualMollieStatus')
            ->with('tr_test123')
            ->willReturn(PaymentStatus::STATUS_PENDING);

        $this->service->expects($this->never())
            ->method('cancelOrderAndRestoreCart');

        $this->service->handleAbandonedPayment(42);
    }

    public function testHandleAbandonedPaymentPaidStatusDoesNotCancel()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->willReturn([
                'cart_id' => '100',
                'transaction_id' => 'tr_test123',
            ]);

        $this->service->method('getActualMollieStatus')
            ->with('tr_test123')
            ->willReturn(PaymentStatus::STATUS_PAID);

        $this->service->expects($this->never())
            ->method('cancelOrderAndRestoreCart');

        $this->service->handleAbandonedPayment(42);
    }

    public function testHandleAbandonedPaymentApiErrorDoesNotCancel()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->willReturn([
                'cart_id' => '100',
                'transaction_id' => 'tr_test123',
            ]);

        $this->service->method('getActualMollieStatus')
            ->with('tr_test123')
            ->willReturn('');

        $this->service->expects($this->never())
            ->method('cancelOrderAndRestoreCart');

        $this->service->handleAbandonedPayment(42);
    }

    public function testHandleAbandonedPaymentExpiredStatusCancelsOrder()
    {
        $this->service->method('findPendingPayByBankPayment')
            ->willReturn([
                'cart_id' => '200',
                'transaction_id' => 'tr_expired',
            ]);

        $this->service->method('getActualMollieStatus')
            ->with('tr_expired')
            ->willReturn(PaymentStatus::STATUS_EXPIRED);

        $this->service->expects($this->once())
            ->method('cancelOrderAndRestoreCart')
            ->with(200, 'tr_expired', PaymentStatus::STATUS_EXPIRED);

        $this->service->handleAbandonedPayment(42);
    }

    private function createRealServiceForPureMethods()
    {
        return $this->getMockBuilder(PayByBankCancellationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActualMollieStatus', 'cancelOrderAndRestoreCart', 'findPendingPayByBankPayment'])
            ->getMock();
    }
}
