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

namespace Mollie\Tests\Unit\Subscription\Handler;

use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Subscription\Api\PaymentApi;
use Mollie\Subscription\Api\Request\UpdateSubscriptionRequest;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\GetSubscriptionData;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Factory\UpdateSubscriptionDataFactory;
use Mollie\Subscription\Handler\SubscriptionPaymentMethodUpdateHandler;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\SecureKeyUtility;
use PHPUnit\Framework\MockObject\MockObject;

class SubscriptionPaymentMethodUpdateHandlerTest extends BaseTestCase
{
    private const CUSTOMER_ID = 1;
    private const CART_ID = 2;
    private const MODULE_NAME = 'mollie';
    private const TRANSACTION_ID = 'tr_test';
    private const SUBSCRIPTION_ID = 'sub_test';

    /** @var SubscriptionApi|MockObject */
    private $subscriptionApi;
    /** @var RecurringOrderRepositoryInterface|MockObject */
    private $recurringOrderRepository;
    /** @var UpdateSubscriptionDataFactory|MockObject */
    private $updateSubscriptionDataFactory;
    /** @var PaymentApi|MockObject */
    private $paymentApi;
    /** @var ClockInterface|MockObject */
    private $clock;
    /** @var GetSubscriptionDataFactory|MockObject */
    private $getSubscriptionDataFactory;
    /** @var \Mollie|MockObject */
    private $module;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscriptionApi = $this->mock(SubscriptionApi::class);
        $this->recurringOrderRepository = $this->mock(RecurringOrderRepositoryInterface::class);
        $this->updateSubscriptionDataFactory = $this->mock(UpdateSubscriptionDataFactory::class);
        $this->paymentApi = $this->mock(PaymentApi::class);
        $this->clock = $this->mock(ClockInterface::class);
        $this->getSubscriptionDataFactory = $this->mock(GetSubscriptionDataFactory::class);
        $this->module = $this->mock(\Mollie::class);
        $this->module->name = self::MODULE_NAME;
    }

    public function testItThrowsWhenSecureKeyDoesNotMatch(): void
    {
        $recurringOrder = $this->buildRecurringOrder();
        $recurringOrder->expects($this->never())->method('update');

        $this->recurringOrderRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with(['mollie_subscription_id' => self::SUBSCRIPTION_ID])
            ->willReturn($recurringOrder);

        $this->getSubscriptionDataFactory
            ->expects($this->once())
            ->method('build')
            ->willReturn($this->mock(GetSubscriptionData::class));

        $this->subscriptionApi
            ->expects($this->once())
            ->method('getSubscription')
            ->willReturn($this->buildSubscription('some-other-secure-key'));

        // Nothing beyond the security check should run.
        $this->paymentApi->expects($this->never())->method('getPayment');
        $this->updateSubscriptionDataFactory->expects($this->never())->method('build');
        $this->subscriptionApi->expects($this->never())->method('updateSubscription');

        $this->expectException(TransactionException::class);
        $this->expectExceptionCode(HttpStatusCode::HTTP_UNAUTHORIZED);

        $this->createHandler()->handle(self::TRANSACTION_ID, self::SUBSCRIPTION_ID);
    }

    public function testItUpdatesPaymentMethodWhenSecureKeyMatches(): void
    {
        $recurringOrder = $this->buildRecurringOrder();
        $recurringOrder->expects($this->once())->method('update');

        $this->recurringOrderRepository
            ->expects($this->once())
            ->method('findOrFail')
            ->with(['mollie_subscription_id' => self::SUBSCRIPTION_ID])
            ->willReturn($recurringOrder);

        $this->getSubscriptionDataFactory
            ->expects($this->once())
            ->method('build')
            ->willReturn($this->mock(GetSubscriptionData::class));

        $expectedKey = SecureKeyUtility::generateReturnKey(self::CUSTOMER_ID, self::CART_ID, self::MODULE_NAME);

        $this->subscriptionApi
            ->expects($this->once())
            ->method('getSubscription')
            ->willReturn($this->buildSubscription($expectedKey));

        /** @var Payment|MockObject $payment */
        $payment = $this->mock(Payment::class);
        $payment->mandateId = 'mdt_test';
        $payment->method = 'creditcard';

        $this->paymentApi
            ->expects($this->once())
            ->method('getPayment')
            ->with(self::TRANSACTION_ID)
            ->willReturn($payment);

        $this->updateSubscriptionDataFactory
            ->expects($this->once())
            ->method('build')
            ->with($recurringOrder, 'mdt_test')
            ->willReturn($this->mock(UpdateSubscriptionRequest::class));

        $this->subscriptionApi
            ->expects($this->exactly(2))
            ->method('updateSubscription')
            ->willReturn($this->buildSubscription($expectedKey, 'sub_new'));

        $this->clock->expects($this->once())->method('getCurrentDate')->willReturn('2024-01-01 00:00:00');

        $this->createHandler()->handle(self::TRANSACTION_ID, self::SUBSCRIPTION_ID);

        self::assertSame('creditcard', $recurringOrder->payment_method);
        self::assertSame('sub_new', $recurringOrder->mollie_subscription_id);
    }

    private function createHandler(): SubscriptionPaymentMethodUpdateHandler
    {
        return new SubscriptionPaymentMethodUpdateHandler(
            $this->subscriptionApi,
            $this->recurringOrderRepository,
            $this->updateSubscriptionDataFactory,
            $this->paymentApi,
            $this->clock,
            $this->getSubscriptionDataFactory,
            $this->module
        );
    }

    /**
     * @return \MolRecurringOrder|MockObject
     */
    private function buildRecurringOrder()
    {
        /** @var \MolRecurringOrder|MockObject $recurringOrder */
        $recurringOrder = $this->mock(\MolRecurringOrder::class);
        $recurringOrder->id = 10;
        $recurringOrder->id_customer = self::CUSTOMER_ID;
        $recurringOrder->id_cart = self::CART_ID;
        $recurringOrder->mollie_customer_id = 'cst_test';
        $recurringOrder->mollie_subscription_id = self::SUBSCRIPTION_ID;

        return $recurringOrder;
    }

    /**
     * @return Subscription|MockObject
     */
    private function buildSubscription(string $secureKey, string $id = 'sub_test')
    {
        /** @var Subscription|MockObject $subscription */
        $subscription = $this->mock(Subscription::class);
        $subscription->id = $id;

        $metadata = new \stdClass();
        $metadata->secure_key = $secureKey;
        $subscription->metadata = $metadata;

        return $subscription;
    }
}
