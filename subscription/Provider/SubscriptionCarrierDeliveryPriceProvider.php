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

use Mollie\Exception\MollieException;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Repository\CountryRepositoryInterface;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Subscription\DTO\SubscriptionCarrierDeliveryPriceData;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionCarrierDeliveryPrice;
use Mollie\Subscription\Exception\MollieSubscriptionException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCarrierDeliveryPriceProvider
{
    /** @var CarrierRepositoryInterface */
    private $carrierRepository;
    /** @var AddressRepositoryInterface */
    private $addressRepository;
    /** @var CustomerRepositoryInterface */
    private $customerRepository;
    /** @var CartRepositoryInterface */
    private $cartRepository;
    /** @var CountryRepositoryInterface */
    private $countryRepository;

    public function __construct(
        CarrierRepositoryInterface $carrierRepository,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $cartRepository,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->carrierRepository = $carrierRepository;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @throws MollieException|MollieSubscriptionException
     */
    public function getPrice(SubscriptionCarrierDeliveryPriceData $data): float
    {
        /** @var \Carrier $carrier */
        $carrier = $this->carrierRepository->findOrFail([
            'id_carrier' => $data->getSubscriptionCarrierId(),
            'active' => 1,
            'deleted' => 0,
        ]);

        /** @var \Cart $cart */
        $cart = $this->cartRepository->findOrFail([
            'id_cart' => $data->getCartId(),
        ]);

        /** @var \Customer $customer */
        $customer = $this->customerRepository->findOrFail([
            'id_customer' => $data->getCustomerId(),
        ]);

        $getAvailableOrderCarriers = $this->carrierRepository->getCarriersForOrder(
            $this->addressRepository->getZoneById($data->getDeliveryAddressId()),
            $customer->getGroups(),
            $cart
        );

        if (!in_array($data->getSubscriptionCarrierId(), array_column($getAvailableOrderCarriers, 'id_carrier'), false)) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToApplySelectedCarrier($data->getSubscriptionCarrierId());
        }

        /** @var \Address $address */
        $address = $this->addressRepository->findOrFail([
            'id_address' => $data->getDeliveryAddressId(),
        ]);

        /** @var \Country $country */
        $country = $this->countryRepository->findOrFail([
            'id_country' => $address->id_country,
        ]);

        /** @var float|bool $deliveryPrice */
        $deliveryPrice = $cart->getPackageShippingCost(
            (int) $carrier->id,
            true,
            $country,
            [$data->getSubscriptionProduct()],
            $this->addressRepository->getZoneById($data->getDeliveryAddressId())
        );

        if (is_bool($deliveryPrice) && !$deliveryPrice) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToGetSelectedCarrierPrice($data->getSubscriptionCarrierId());
        }

        return (float) $deliveryPrice;
    }
}
