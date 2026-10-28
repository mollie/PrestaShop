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

namespace Mollie\Subscription\Provider;

use Cart;
use Combination;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Subscription\DTO\SubscriptionOrderAmountProviderData;
use Mollie\Subscription\Validator\SubscriptionProductValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * What the customer agrees to before paying for a subscription: the amount charged on every
 * renewal, how often, and from when. Built with the same providers as the subscription created
 * after the first payment, so the checkout shows the amount Mollie will actually charge
 * (product price plus the delivery price of the subscription carrier for the delivery address).
 */
class SubscriptionCheckoutSummaryProvider
{
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var SubscriptionProductValidator */
    private $subscriptionProductValidator;
    /** @var SubscriptionOrderAmountProvider */
    private $subscriptionOrderAmountProvider;
    /** @var SubscriptionIntervalProvider */
    private $subscriptionIntervalProvider;
    /** @var SubscriptionStartDateProvider */
    private $subscriptionStartDateProvider;

    public function __construct(
        ConfigurationAdapter $configuration,
        SubscriptionProductValidator $subscriptionProductValidator,
        SubscriptionOrderAmountProvider $subscriptionOrderAmountProvider,
        SubscriptionIntervalProvider $subscriptionIntervalProvider,
        SubscriptionStartDateProvider $subscriptionStartDateProvider
    ) {
        $this->configuration = $configuration;
        $this->subscriptionProductValidator = $subscriptionProductValidator;
        $this->subscriptionOrderAmountProvider = $subscriptionOrderAmountProvider;
        $this->subscriptionIntervalProvider = $subscriptionIntervalProvider;
        $this->subscriptionStartDateProvider = $subscriptionStartDateProvider;
    }

    /**
     * @return array{amount: float, currency: string, interval: string, start_date: string}|null
     *                                                                                             null when the cart holds no subscription product or has no delivery address yet
     *
     * @throws \Throwable when the renewal amount cannot be computed (carrier not available for the address...)
     */
    public function get(Cart $cart): ?array
    {
        if (!(int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ENABLED) || !(int) $cart->id_address_delivery) {
            return null;
        }

        foreach ($cart->getProducts() as $product) {
            $productAttributeId = (int) $product['id_product_attribute'];

            if (!$this->subscriptionProductValidator->validate($productAttributeId)) {
                continue;
            }

            $amount = $this->subscriptionOrderAmountProvider->get(
                SubscriptionOrderAmountProviderData::create(
                    (int) $cart->id_address_delivery,
                    (int) $cart->id,
                    (int) $cart->id_customer,
                    $product,
                    (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID),
                    (int) $cart->id_currency,
                    (float) $product['total_wt']
                )
            );

            return [
                'amount' => $amount->getValue(),
                'currency' => (string) $amount->toArray()['currency'],
                'interval' => $this->subscriptionIntervalProvider->getSubscriptionInterval($productAttributeId)->jsonSerialize(),
                'start_date' => $this->subscriptionStartDateProvider->getSubscriptionStartDate(new Combination($productAttributeId)),
            ];
        }

        return null;
    }
}
