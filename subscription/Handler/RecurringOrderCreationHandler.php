<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use Mollie;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Service\OrderStatusService;
use Mollie\Service\PaymentMethodService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
use MolRecurringOrder;
use Order;

class RecurringOrderCreationHandler
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var GetSubscriptionDataFactory */
    private $subscriptionDataFactory;
    /** @var RecurringOrderRepositoryInterface */
    private $recurringOrderRepository;
    /** @var Mollie */
    private $mollie;
    /** @var MollieOrderCreationService */
    private $mollieOrderCreationService;
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;
    /** @var OrderStatusService */
    private $orderStatusService;
    /** @var PaymentMethodService */
    private $paymentMethodService;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        GetSubscriptionDataFactory $subscriptionDataFactory,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        Mollie $mollie,
        MollieOrderCreationService $mollieOrderCreationService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderStatusService $orderStatusService,
        PaymentMethodService $paymentMethodService
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->mollie = $mollie;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderStatusService = $orderStatusService;
        $this->paymentMethodService = $paymentMethodService;
    }

    public function handle(string $transactionId)
    {
        $transaction = $this->mollie->getApiClient()->payments->get($transactionId);
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['mollie_subscription_id' => $transaction->subscriptionId]);
        $subscriptionData = $this->subscriptionDataFactory->build((int) $recurringOrder->id);
        $subscription = $this->subscriptionApi->getSubscription($subscriptionData);

        $key = Mollie\Utility\SecureKeyUtility::generateReturnKey(
            $recurringOrder->id_customer,
            $recurringOrder->id_cart,
            $this->mollie->name
        );

        if ($key !== $subscription->metadata->secure_key) {
            throw new TransactionException('Security key is incorrect.', HttpStatusCode::HTTP_UNAUTHORIZED);
        }

        $existingTransaction = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transaction->id);

        switch (true) {
            case $existingTransaction:
                $this->updateOrderStatus($transaction, (int) $existingTransaction['order_id']);
                break;
            case $transaction->status === PaymentStatus::STATUS_PAID:
                $this->createSubscription($transaction, $recurringOrder, $subscription);
                break;
            default:
                break;
        }

        return 'OK';
    }

    private function createSubscription(Payment $transaction, MolRecurringOrder $recurringOrder, MollieSubscription $subscription)
    {
        $cart = new Cart($recurringOrder->id_cart);
        $newCart = $cart->duplicate();
        if (!$newCart['success']) {
            return;
        }

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($transaction);

        $methodName = $paymentMethod->method_name ?: Config::$methods[$transaction->method];

        $newCart = $newCart['cart'];
        $this->mollie->validateOrder(
            (int) $newCart->id,
            Config::getStatuses()[$transaction->status],
            (float) $subscription->amount->value,
            'subscription/' . $methodName,
            null,
            ['transaction_id' => $transaction->id],
            null,
            false,
            $newCart->secure_key
        );

        $orderId = Order::getIdByCartId((int) $newCart->id);
        $order = new Order($orderId);

        $this->mollieOrderCreationService->createMolliePayment($transaction, $newCart->id, $order->reference, $orderId);
    }

    private function updateOrderStatus(Payment $transaction, int $orderId)
    {
        $this->orderStatusService->setOrderStatus($orderId, $transaction->status);
    }
}
