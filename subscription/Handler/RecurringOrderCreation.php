<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Subscription\Api\Subscription;
use Mollie\Subscription\Exception\NotImplementedException;
use Mollie\Subscription\Factory\CreateSubscriptionData;
use Mollie\Subscription\Utility\ClockInterface;
use Order;

class RecurringOrderCreation
{
    /** @var ClockInterface */
    private $clock;

    /** @var Subscription */
    private $subscriptionApi;

    /** @var CreateSubscriptionData */
    private $subscriptionDataFactory;

    public function __construct(
        ClockInterface $clock,
        Subscription $subscriptionApi,
        CreateSubscriptionData $subscriptionDataFactory
    ) {
        $this->clock = $clock;
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriptionDataFactory = $subscriptionDataFactory;
    }

    public function handle(Order $order)
    {
        $subscriptionData = $this->subscriptionDataFactory->build($order);
        $subscription = $this->subscriptionApi->subscribeOrder($subscriptionData);

        $recurringOrder = new \MolSubRecurringOrder();
        $recurringOrder->id_order = $order->id;
        $recurringOrder->description = $subscription->description;
        $recurringOrder->status = $subscription->status;
        $recurringOrder->quantity = '0'; //todo: check if we really need it
        $recurringOrder->amount = $subscription->amount->value;
        $recurringOrder->currency_iso = $subscription->amount->currency;
        $recurringOrder->next_payment = $subscription->nextPaymentDate;
        $recurringOrder->reminder_at = $subscription->nextPaymentDate; //todo: add logic to get reminder date when remidner is done
        $recurringOrder->cancelled_at = $subscription->canceledAt;
        $recurringOrder->mollie_sub_id = $subscription->id;
        $recurringOrder->mollie_customer_id = $subscription->customerId;
        $recurringOrder->date_add = $this->clock->getCurrentDate();
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->add();
    }
}
