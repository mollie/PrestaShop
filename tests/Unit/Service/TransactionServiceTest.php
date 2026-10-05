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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\TransactionException;
use Mollie\Factory\ModuleFactory;
use Mollie\Handler\Order\OrderCreationHandler;
use Mollie\Handler\Order\OrderPaymentFeeHandler;
use Mollie\Handler\Shipment\ShipmentSenderHandlerInterface;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ExceptionService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Service\TransactionService;
use Order;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    /** @var PaymentMethodRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethodRepository;
    /** @var OrderStatusService|\PHPUnit\Framework\MockObject\MockObject */
    private $orderStatusService;
    /** @var MollieOrderCreationService|\PHPUnit\Framework\MockObject\MockObject */
    private $mollieOrderCreationService;
    /** @var OrderCreationHandler|\PHPUnit\Framework\MockObject\MockObject */
    private $orderCreationHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->orderStatusService = $this->createMock(OrderStatusService::class);
        $this->mollieOrderCreationService = $this->createMock(MollieOrderCreationService::class);
        $this->orderCreationHandler = $this->createMock(OrderCreationHandler::class);
    }

    public function testPaymentFlaggedWithWrongAmountIsNotProcessed(): void
    {
        $this->paymentMethodRepository->method('getPaymentBy')->willReturn([
            'transaction_id' => 'tr_rejected',
            'cart_id' => 25,
            'order_id' => 0,
            'reason' => Config::WRONG_AMOUNT_REASON,
        ]);

        $this->mollieOrderCreationService->expects($this->never())->method('createMolliePayment');
        $this->orderCreationHandler->expects($this->never())->method('createOrder');
        $this->orderStatusService->expects($this->never())->method('setOrderStatus');

        $apiPayment = $this->apiPayment('tr_rejected');

        $result = $this->service()->processTransaction($apiPayment);

        $this->assertSame($apiPayment, $result);
    }

    public function testPaymentWithoutReasonIsProcessed(): void
    {
        $this->paymentMethodRepository->method('getPaymentBy')->willReturn([
            'transaction_id' => 'tr_regular',
            'cart_id' => 25,
            'order_id' => 0,
            'reason' => null,
        ]);

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Security key is incorrect.');

        $this->service()->processTransaction($this->apiPayment('tr_regular'));
    }

    /**
     * @dataProvider conversionRateProvider
     *
     * @param float $orderConversionRate
     */
    public function testOrderPaymentTakesConversionRateFromOrder($orderConversionRate)
    {
        $order = new Order();
        $order->reference = 'ABCDEFGHI';
        $order->conversion_rate = $orderConversionRate;

        $orderPayment = TransactionService::buildOrderPayment([
            'amount' => '24.20',
            'paymentName' => 'Bank transfer',
            'transactionId' => 'tr_test841',
            'currency' => 'EUR',
        ], $order);

        $this->assertSame($orderConversionRate, $orderPayment->conversion_rate);
        $this->assertSame('ABCDEFGHI', $orderPayment->order_reference);
        $this->assertSame('24.20', $orderPayment->amount);
        $this->assertSame('Bank transfer', $orderPayment->payment_method);
        $this->assertSame('tr_test841', $orderPayment->transaction_id);
    }

    public function conversionRateProvider()
    {
        return [
            'default currency' => [1.0],
            'foreign currency' => [1.0864],
        ];
    }

    private function service(): TransactionService
    {
        $module = $this->getMockBuilder(\Mollie::class)->disableOriginalConstructor()->getMock();
        $moduleFactory = $this->createMock(ModuleFactory::class);
        $moduleFactory->method('getModule')->willReturn($module);

        return new TransactionService(
            $moduleFactory,
            $this->orderStatusService,
            $this->paymentMethodRepository,
            $this->orderCreationHandler,
            $this->createMock(PaymentMethodService::class),
            $this->mollieOrderCreationService,
            $this->createMock(OrderPaymentFeeHandler::class),
            $this->createMock(ShipmentSenderHandlerInterface::class),
            $this->createMock(PrestaLoggerInterface::class),
            $this->createMock(ExceptionService::class),
            $this->createMock(ConfigurationAdapter::class),
            $this->createMock(OrderRepositoryInterface::class)
        );
    }

    private function apiPayment(string $id): \stdClass
    {
        $payment = new \stdClass();
        $payment->id = $id;
        $payment->resource = 'payment';
        $payment->status = 'paid';
        $payment->description = 'ORDER-1';
        $payment->mandateId = null;
        $payment->metadata = new \stdClass();
        $payment->metadata->cart_id = 999999;
        $payment->metadata->secure_key = 'not-the-real-key';

        return $payment;
    }
}
