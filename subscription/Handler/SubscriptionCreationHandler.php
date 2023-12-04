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

use Mollie\Subscription\Action\CreateRecurringOrderAction;
use Mollie\Subscription\Action\CreateRecurringOrdersProductAction;
use Mollie\Subscription\Api\SubscriptionApi;
use Mollie\Subscription\DTO\CreateRecurringOrderData;
use Mollie\Subscription\DTO\CreateRecurringOrdersProductData;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Validator\SubscriptionProductValidator;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCreationHandler
{
    /** @var SubscriptionApi */
    private $subscriptionApi;
    /** @var CreateSubscriptionDataFactory */
    private $createSubscriptionDataFactory;
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;
    /** @var CreateRecurringOrdersProductAction */
    private $createRecurringOrdersProductAction;
    /** @var CreateRecurringOrderAction */
    private $createRecurringOrderAction;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        CreateSubscriptionDataFactory $subscriptionDataFactory,
        SubscriptionProductValidator $subscriptionProductValidator,
        CreateRecurringOrdersProductAction $createRecurringOrdersProductAction,
        CreateRecurringOrderAction $createRecurringOrderAction
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->createSubscriptionDataFactory = $subscriptionDataFactory;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
        $this->createRecurringOrdersProductAction = $createRecurringOrdersProductAction;
        $this->createRecurringOrderAction = $createRecurringOrderAction;
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

        try {
            $this->createRecurringOrderAction->run(CreateRecurringOrderData::create(
                (int) $recurringOrdersProduct->id,
                (int) $order->id,
                (int) $order->id_cart,
                (int) $order->id_currency,
                (int) $order->id_customer,
                (int) $order->id_address_delivery,
                (int) $order->id_address_invoice,
                (string) $subscription->description,
                (string) $subscription->status,
                (float) $subscription->amount->value,
                $method,
                (string) $subscription->nextPaymentDate,
                (string) $subscription->nextPaymentDate, // TODO: add logic to get reminder date when reminder is done
                (string) $subscription->canceledAt,
                (string) $subscription->id,
                (string) $subscription->customerId
            ));
        } catch (\Throwable $exception) {
            // TODO throw different exception

            throw $exception;
        }
    }
}
