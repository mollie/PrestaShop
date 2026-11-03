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
use Mollie\Factory\ModuleFactory;
use Mollie\Handler\CartRule\CartRuleQuantityResetHandlerInterface;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\PayByBankCancellationService;
use PHPUnit\Framework\TestCase;

class PayByBankCancellationServiceTest extends TestCase
{
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

    /**
     * @dataProvider belongsToOrderDataProvider
     *
     * @param string $orderModule
     * @param int $paymentOrderId
     * @param bool $expected
     */
    public function testBelongsToOrder($orderModule, $paymentOrderId, $expected)
    {
        $order = $this->getMockBuilder(\Order::class)->disableOriginalConstructor()->getMock();
        $order->id = 5;
        $order->module = $orderModule;

        $payment = ['order_id' => (string) $paymentOrderId];

        self::assertSame($expected, $this->createService()->belongsToOrder($payment, $order));
    }

    public function belongsToOrderDataProvider()
    {
        return [
            'order paid with another module' => ['ps_wirepayment', 0, false],
            'order of another module linked to the payment' => ['ps_wirepayment', 5, false],
            'mollie order, abandoned payment never linked' => ['mollie', 0, false],
            'mollie order paid by another mollie payment' => ['mollie', 7, false],
            'mollie order created for this payment' => ['mollie', 5, true],
        ];
    }

    private function createService()
    {
        $module = $this->getMockBuilder(\Mollie::class)->disableOriginalConstructor()->getMock();
        $module->name = 'mollie';
        $moduleFactory = $this->createMock(ModuleFactory::class);
        $moduleFactory->method('getModule')->willReturn($module);

        return new PayByBankCancellationService(
            $moduleFactory,
            $this->createMock(LoggerInterface::class),
            $this->createMock(PaymentMethodRepository::class),
            $this->createMock(CartRuleQuantityResetHandlerInterface::class)
        );
    }

    private function createRealServiceForPureMethods()
    {
        return $this->getMockBuilder(PayByBankCancellationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getActualMollieStatus', 'cancelOrderAndRestoreCart'])
            ->getMock();
    }
}
