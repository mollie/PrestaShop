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

namespace Mollie\Subscription\Provider;

use Mollie\Shared\Infrastructure\Repository\CurrencyRepositoryInterface;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\SubscriptionCarrierDeliveryPriceData;
use Mollie\Subscription\DTO\SubscriptionOrderAmountProviderData;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionOrderAmount;
use Mollie\Subscription\Exception\MollieSubscriptionException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionOrderAmountProvider
{
    /** @var SubscriptionCarrierDeliveryPriceProvider */
    private $subscriptionCarrierDeliveryPriceProvider;
    /** @var CurrencyRepositoryInterface */
    private $currencyRepository;

    public function __construct(
        SubscriptionCarrierDeliveryPriceProvider $subscriptionCarrierDeliveryPriceProvider,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->subscriptionCarrierDeliveryPriceProvider = $subscriptionCarrierDeliveryPriceProvider;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @throws MollieSubscriptionException
     */
    public function get(SubscriptionOrderAmountProviderData $data): Amount
    {
        try {
            $deliveryPrice = $this->subscriptionCarrierDeliveryPriceProvider->getPrice(
                new SubscriptionCarrierDeliveryPriceData(
                    $data->getAddressDeliveryId(),
                    $data->getCartId(),
                    $data->getCustomerId(),
                    $data->getSubscriptionProduct(),
                    $data->getSubscriptionCarrierId()
                )
            );
        } catch (\Throwable $exception) {
            throw CouldNotProvideSubscriptionOrderAmount::failedToProvideCarrierDeliveryPrice($exception);
        }

        $orderTotal = (float) $data->getSubscriptionProduct()['total_price_tax_incl'] + $deliveryPrice;

        /** @var \Currency|null $currency */
        $currency = $this->currencyRepository->findOneBy([
            'id_currency' => $data->getCurrencyId(),
        ]);

        if (!$currency) {
            throw CouldNotProvideSubscriptionOrderAmount::failedToFindCurrency((int) $data->getCurrencyId());
        }

        return new Amount($orderTotal, $currency->iso_code);
    }
}
