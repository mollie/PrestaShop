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

declare(strict_types=1);

namespace Mollie\Subscription\Handler;

use Mollie\Api\Resources\Subscription;
use Mollie\Subscription\Action\CreateRecurringOrdersProductAction;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\CreateRecurringOrdersProductData;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use MolRecurringOrder;
use MolRecurringOrdersProduct;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCreationHandler
{
    /** @var ClockInterface */
    private $clock;

    /** @var SubscriptionApi */
    private $subscriptionApi;

    /** @var CreateSubscriptionDataFactory */
    private $createSubscriptionDataFactory;
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;
    /** @var CreateRecurringOrdersProductAction */
    private $createRecurringOrdersProductAction;

    public function __construct(
        ClockInterface $clock,
        SubscriptionApi $subscriptionApi,
        CreateSubscriptionDataFactory $subscriptionDataFactory,
        SubscriptionProductValidator $subscriptionProductValidator,
        CreateRecurringOrdersProductAction $createRecurringOrdersProductAction
    ) {
        $this->clock = $clock;
        $this->subscriptionApi = $subscriptionApi;
        $this->createSubscriptionDataFactory = $subscriptionDataFactory;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
        $this->createRecurringOrdersProductAction = $createRecurringOrdersProductAction;
    }

    /**
     * @throws \Throwable
     */
    public function handle(Order $order, string $method): void
    {
        $products = $order->getCartProducts();
        $subscriptionProduct = [];

        foreach ($products as $product) {
            if (!$this->subscriptionProductValidator->validate((int) $product['id_product_attribute'])) {
                continue;
            }

            $subscriptionProduct = $product;

            break;
        }

        $subscriptionData = $this->createSubscriptionDataFactory->build($order, $subscriptionProduct);
        $subscription = $this->subscriptionApi->subscribeOrder($subscriptionData);

        try {
            $recurringOrdersProduct = $this->createRecurringOrdersProductAction->run(
                CreateRecurringOrdersProductData::create(
                    (int) $subscriptionProduct['id_product'],
                    (int) $subscriptionProduct['id_product_attribute'],
                    (int) $subscriptionProduct['product_quantity'],
                    (float) $subscriptionProduct['unit_price_tax_excl']
                )
            );
        } catch (\Throwable $exception) {
            // TODO throw different exception

            throw $exception;
        }

        $this->createRecurringOrder($recurringOrdersProduct, $order, $subscription, $method);
    }

    private function createRecurringOrder(MolRecurringOrdersProduct $recurringOrdersProduct, Order $order, Subscription $subscription, string $method): void
    {
        $recurringOrder = new MolRecurringOrder();
        $recurringOrder->id_mol_recurring_orders_product = $recurringOrdersProduct->id;
        $recurringOrder->id_order = $order->id;
        $recurringOrder->id_cart = $order->id_cart;
        $recurringOrder->id_currency = $order->id_currency;
        $recurringOrder->id_customer = $order->id_customer;
        $recurringOrder->id_address_delivery = $order->id_address_delivery;
        $recurringOrder->id_address_invoice = $order->id_address_invoice;
        $recurringOrder->description = $subscription->description;
        $recurringOrder->status = $subscription->status;
        $recurringOrder->total_tax_incl = (float) $subscription->amount->value;
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
