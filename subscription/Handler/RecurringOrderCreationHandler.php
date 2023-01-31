<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Cart;
use Mollie;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Service\MollieOrderCreationService;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\GetSubscriptionDataFactory;
use Mollie\Subscription\Repository\RecurringOrderRepositoryInterface;
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

    public function __construct(
        SubscriptionApi $subscriptionApi,
        GetSubscriptionDataFactory $subscriptionDataFactory,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        Mollie $mollie,
        MollieOrderCreationService $mollieOrderCreationService
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->mollie = $mollie;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
    }

    public function handle(string $transactionId)
    {
        $transaction = $this->mollie->getApiClient()->payments->get($transactionId);
        $recurringOrder = $this->recurringOrderRepository->findOneBy(['mollie_subscription_id' => $transaction->subscriptionId]);
        $subscriptionData = $this->subscriptionDataFactory->build((int) $recurringOrder->id);
        $subscription = $this->subscriptionApi->getSubscription($subscriptionData);

        $cart = new Cart($recurringOrder->id_cart);
        $newCart = $cart->duplicate();
        if (!$newCart['success']) {
            return;
        }

        $newCart = $newCart['cart'];
        $this->mollie->validateOrder(
            (int) $newCart->id,
            Config::getStatuses()[PaymentStatus::STATUS_PAID],
            (float) $subscription->amount->value,
            $transaction->method,
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
}
