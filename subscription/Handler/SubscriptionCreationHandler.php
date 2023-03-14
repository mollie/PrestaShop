<?php

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Api\Resources\Subscription;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Utility\ClockInterface;
use MolRecurringOrder;
use MolRecurringOrdersProduct;
use Order;

class SubscriptionCreationHandler
{
    /** @var ClockInterface */
    private $clock;

    /** @var SubscriptionApi */
    private $subscriptionApi;

    /** @var CreateSubscriptionDataFactory */
    private $createSubscriptionDataFactory;

    public function __construct(
        ClockInterface $clock,
        SubscriptionApi $subscriptionApi,
        CreateSubscriptionDataFactory $subscriptionDataFactory
    ) {
        $this->clock = $clock;
        $this->subscriptionApi = $subscriptionApi;
        $this->createSubscriptionDataFactory = $subscriptionDataFactory;
    }

    public function handle(Order $order, string $method)
    {
        $subscriptionData = $this->createSubscriptionDataFactory->build($order);
        $subscription = $this->subscriptionApi->subscribeOrder($subscriptionData);

        $products = $order->getProducts();
        $product = reset($products);

        $recurringOrdersProduct = $this->createRecurringOrdersProduct($product);

        $this->createRecurringOrder($recurringOrdersProduct, $order, $subscription, $method);
    }

    private function createRecurringOrdersProduct(array $product): MolRecurringOrdersProduct
    {
        $recurringOrdersProduct = new MolRecurringOrdersProduct();
        $recurringOrdersProduct->id_product = $product['id_product'];
        $recurringOrdersProduct->id_product_attribute = $product['product_attribute_id'];
        $recurringOrdersProduct->quantity = $product['product_quantity'];
        $recurringOrdersProduct->unit_price = $product['product_price'];
        $recurringOrdersProduct->add();

        return $recurringOrdersProduct;
    }

    private function createRecurringOrder(MolRecurringOrdersProduct $recurringOrdersProduct, Order $order, Subscription $subscription, string $method): void
    {
        $recurringOrder = new MolRecurringOrder();
        $recurringOrder->id_mol_recurring_orders_product = $recurringOrdersProduct->id;
        $recurringOrder->id_order = $order->id;
        $recurringOrder->id_cart = $order->id_cart;
        $recurringOrder->id_currency = $order->id_currency;
        $recurringOrder->id_customer = $order->id_customer;
        $recurringOrder->description = $subscription->description;
        $recurringOrder->status = $subscription->status;
        $recurringOrder->payment_method = $method;
        $recurringOrder->next_payment = $subscription->nextPaymentDate;
        $recurringOrder->reminder_at = $subscription->nextPaymentDate; //todo: add logic to get reminder date when reminder is done
        $recurringOrder->cancelled_at = $subscription->canceledAt;
        $recurringOrder->mollie_subscription_id = $subscription->id;
        $recurringOrder->mollie_customer_id = $subscription->customerId;
        $recurringOrder->date_add = $this->clock->getDateFromTimeStamp(strtotime($subscription->createdAt));
        $recurringOrder->date_update = $this->clock->getCurrentDate();
        $recurringOrder->add();
    }
}
