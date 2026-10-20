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

namespace Mollie\Tests\Integration\Handler\Order;

use Mollie\Api\Resources\Payment;
use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Factory\ModuleFactory;
use Mollie\Handler\Order\OrderCreationHandler;
use Mollie\Handler\Order\OrderPaymentFeeHandler;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressCartCarrierService;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\MollieOrderNumberService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Handler\SubscriptionCreationHandler;
use Mollie\Subscription\Validator\SubscriptionOrderValidator;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Validator\PaymentAmountValidator;

class OrderCreationHandlerExpressTest extends BaseTestCase
{
    /** A cart id no fixture shop has, so the handler works on an empty cart with a 0.00 total. */
    const EMPTY_CART_ID = 987654321;

    /** @var PaymentFeeProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentFeeProvider;
    /** @var PaymentMethodRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethodRepository;
    /** @var \Mollie|\PHPUnit\Framework\MockObject\MockObject */
    private $module;
    /** @var OrderCreationHandler */
    private $handler;

    protected function setUp()
    {
        parent::setUp();

        $this->module = $this->mock(\Mollie::class);
        $moduleFactory = $this->mock(ModuleFactory::class);
        $moduleFactory->method('getModule')->willReturn($this->module);

        $paymentMethodService = $this->mock(PaymentMethodService::class);
        $paymentMethodService->method('getPaymentMethod')->willReturn(new \MolPaymentMethod());
        $paymentMethodService->method('getPaymentMethodName')->willReturn('PayPal');

        $this->paymentFeeProvider = $this->mock(PaymentFeeProviderInterface::class);
        $this->paymentMethodRepository = $this->mock(PaymentMethodRepositoryInterface::class);

        $orderRepository = $this->mock(OrderRepositoryInterface::class);
        $orderRepository->method('getOrderIdByCartId')->willReturn(0);

        $subscriptionOrder = $this->mock(SubscriptionOrderValidator::class);
        $subscriptionOrder->method('validate')->willReturn(false);

        $this->handler = new OrderCreationHandler(
            $moduleFactory,
            $this->paymentMethodRepository,
            $paymentMethodService,
            $this->mock(OrderPaymentFeeHandler::class),
            $this->mock(OrderStatusService::class),
            $this->mock(SubscriptionCreationHandler::class),
            $subscriptionOrder,
            $this->paymentFeeProvider,
            $this->mock(PrestaLoggerInterface::class),
            $orderRepository,
            $this->mock(MollieOrderCreationService::class),
            new PaymentAmountValidator(),
            $this->mock(MollieOrderNumberService::class),
            $this->mock(ExpressCartCarrierService::class)
        );
    }

    private function mock(string $className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function payment(string $amount, bool $express, string $currency = ''): Payment
    {
        /** @var Payment|\PHPUnit\Framework\MockObject\MockObject $payment */
        $payment = $this->mock(Payment::class);
        $payment->id = 'tr_express';
        $payment->status = 'paid';
        $payment->method = 'paypal';
        $payment->resource = 'payment';
        $payment->amount = (object) ['currency' => $currency ?: $this->cartCurrencyIso(), 'value' => $amount];
        $payment->metadata = (object) array_filter([
            'cart_id' => self::EMPTY_CART_ID,
            Config::EXPRESS_METADATA_FLAG => $express ? 1 : 0,
        ]);

        return $payment;
    }

    public function testAnExpressPaymentNeverGetsAPaymentFee()
    {
        $payment = $this->payment('0.00', true);

        $this->paymentFeeProvider->expects($this->never())->method('getPaymentFee');
        $payment->expects($this->never())->method('refund');
        $this->module->expects($this->once())->method('validateOrder');

        $this->handler->createOrder($payment, self::EMPTY_CART_ID);
    }

    public function testAnExpressPaymentWhoseAmountNoLongerMatchesTheCartCreatesAnErrorOrder()
    {
        $payment = $this->payment('10.00', true);

        $payment->expects($this->never())->method('refund');
        $this->paymentMethodRepository->expects($this->once())->method('updatePaymentReason')->with('tr_express', Config::WRONG_AMOUNT_REASON);
        $this->module->expects($this->once())->method('validateOrder')
            ->with($this->anything(), (int) \Configuration::get('PS_OS_ERROR'), 10.00);

        // validateOrder is mocked, so no order exists to attach the payment to and the handler stops there.
        $this->expectException(\PrestaShopException::class);

        $this->handler->createOrder($payment, self::EMPTY_CART_ID);
    }

    private function cartCurrencyIso(): string
    {
        return (string) (new \Currency((int) (new \Cart(self::EMPTY_CART_ID))->id_currency))->iso_code;
    }

    public function testAnExpressPaymentInAnotherCurrencyCreatesAnErrorOrderEvenWhenTheNumberMatches()
    {
        $otherCurrency = 'JPY' === $this->cartCurrencyIso() ? 'CHF' : 'JPY';
        $payment = $this->payment('0.00', true, $otherCurrency);

        $payment->expects($this->never())->method('refund');
        $this->module->expects($this->once())->method('validateOrder')
            ->with($this->anything(), (int) \Configuration::get('PS_OS_ERROR'));

        // validateOrder is mocked, so no order exists to attach the payment to and the handler stops there.
        $this->expectException(\PrestaShopException::class);

        $this->handler->createOrder($payment, self::EMPTY_CART_ID);
    }

    public function testARegularPaymentStillAsksTheFeeProvider()
    {
        $payment = $this->payment('0.00', false);

        $this->paymentFeeProvider->expects($this->once())->method('getPaymentFee')->willReturn(new PaymentFeeData(0.00, 0.00, 0.00, false));
        $this->module->expects($this->once())->method('validateOrder');

        $this->handler->createOrder($payment, self::EMPTY_CART_ID);
    }
}
