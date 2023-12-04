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
use Mollie\Subscription\Exception\CouldNotCreateSubscription;
use Mollie\Subscription\Exception\MollieSubscriptionException;
use Mollie\Subscription\Factory\CreateSubscriptionDataFactory;
use Mollie\Subscription\Provider\SubscriptionProductProvider;
use Mollie\Subscription\Validator\SubscriptionSettingsValidator;
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
    /** @var SubscriptionSettingsValidator */
    private $subscriptionSettingsValidator;
    /** @var CreateRecurringOrdersProductAction */
    private $createRecurringOrdersProductAction;
    /** @var CreateRecurringOrderAction */
    private $createRecurringOrderAction;
    /** @var SubscriptionProductProvider */
    private $subscriptionProductProvider;

    public function __construct(
        SubscriptionApi $subscriptionApi,
        CreateSubscriptionDataFactory $subscriptionDataFactory,
        SubscriptionSettingsValidator $subscriptionSettingsValidator,
        CreateRecurringOrdersProductAction $createRecurringOrdersProductAction,
        CreateRecurringOrderAction $createRecurringOrderAction,
        SubscriptionProductProvider $subscriptionProductProvider
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->createSubscriptionDataFactory = $subscriptionDataFactory;
        $this->subscriptionSettingsValidator = $subscriptionSettingsValidator;
        $this->createRecurringOrdersProductAction = $createRecurringOrdersProductAction;
        $this->createRecurringOrderAction = $createRecurringOrderAction;
        $this->subscriptionProductProvider = $subscriptionProductProvider;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function handle(Order $order, string $method): void
    {
        try {
            $this->subscriptionSettingsValidator->validate();
        } catch (\Throwable $exception) {
            throw CouldNotCreateSubscription::invalidSubscriptionSettings($exception);
        }

        $subscriptionProduct = $this->subscriptionProductProvider->getProduct($order->getCartProducts());

        if (empty($subscriptionProduct)) {
            throw CouldNotCreateSubscription::failedToFindSubscriptionProduct();
        }

        try {
            $subscriptionData = $this->createSubscriptionDataFactory->build($order, $subscriptionProduct);
        } catch (\Throwable $exception) {
            throw CouldNotCreateSubscription::failedToCreateSubscriptionData($exception);
        }

        try {
            $subscription = $this->subscriptionApi->subscribeOrder($subscriptionData);
        } catch (\Throwable $exception) {
            throw CouldNotCreateSubscription::failedToSubscribeOrder($exception);
        }

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
            throw CouldNotCreateSubscription::failedToCreateRecurringOrdersProduct($exception);
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
            throw CouldNotCreateSubscription::failedToCreateRecurringOrder($exception);
        }
    }
}
