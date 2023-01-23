<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Utility\ClockInterface;
use MolRecurringOrder;
use Order;

class SubscriptionCreationHandler
{
    /** @var ClockInterface */
    private $clock;

    /** @var SubscriptionApi */
    private $subscriptionApi;

    /** @var CreateSubscriptionDataFactory */
    private $subscriptionDataFactory;

    public function __construct(
        ClockInterface $clock,
        SubscriptionApi $subscriptionApi,
        CreateSubscriptionDataFactory $subscriptionDataFactory
    ) {
        $this->clock = $clock;
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
    }

    public function handle(Order $order)
    {
        $subscriptionData = $this->subscriptionDataFactory->build($order);
        $subscription = $this->subscriptionApi->subscribeOrder($subscriptionData);

        $recurringOrder = new MolRecurringOrder();
        $recurringOrder->id_order = $order->id;
        $recurringOrder->id_cart = $order->id_cart;
        $recurringOrder->description = $subscription->description;
        $recurringOrder->status = $subscription->status;
        $recurringOrder->quantity = '0'; //todo: check if we really need it
        $recurringOrder->amount = $subscription->amount->value;
        $recurringOrder->currency_iso = $subscription->amount->currency;
        $recurringOrder->next_payment = $subscription->nextPaymentDate;
        $recurringOrder->reminder_at = $subscription->nextPaymentDate; //todo: add logic to get reminder date when remidner is done
        $recurringOrder->cancelled_at = $subscription->canceledAt;
        $recurringOrder->mollie_subscription_id = $subscription->id;
        $recurringOrder->mollie_customer_id = $subscription->customerId;
        $recurringOrder->date_add = $this->clock->getDateFromTimeStamp(strtotime($subscription->createdAt));
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->add();
    }
}
