<?php

namespace Mollie\Subscription\Provider;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Repository\CountryRepositoryInterface;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionCarrierDeliveryPrice;

class SubscriptionCarrierDeliveryPriceProvider
{
    /** @var ConfigurationAdapter */
    private $configuration;
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
        ConfigurationAdapter $configuration,
        CarrierRepositoryInterface $carrierRepository,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        CartRepositoryInterface $cartRepository,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->configuration = $configuration;
        $this->carrierRepository = $carrierRepository;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @throws CouldNotProvideSubscriptionCarrierDeliveryPrice
     */
    public function getPrice(int $addressDeliveryId, int $cartId, int $customerId, array $subscriptionProduct): float
    {
        $subscriptionCarrierId = (int) $this->configuration->get(Config::MOLLIE_SUBSCRIPTION_ORDER_CARRIER_ID);

        /** @var \Carrier|null $carrier */
        $carrier = $this->carrierRepository->findOneBy([
            'id_carrier' => $subscriptionCarrierId,
            'active' => 1,
            'deleted' => 0,
        ]);

        if (!$carrier) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindSelectedCarrier();
        }

        /** @var \Cart|null $cart */
        $cart = $this->cartRepository->findOneBy([
            'id_cart' => $cartId,
        ]);

        if (!$cart) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindOrderCart();
        }

        /** @var \Customer|null $customer */
        $customer = $this->customerRepository->findOneBy([
            'id_customer' => $customerId,
        ]);

        if (!$customer) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindOrderCustomer();
        }

        $getAvailableOrderCarriers = $this->carrierRepository->getCarriersForOrder(
            $this->addressRepository->getZoneById($addressDeliveryId),
            $customer->getGroups(),
            $cart
        );

        if (!in_array($subscriptionCarrierId, array_column($getAvailableOrderCarriers, 'id_carrier'), false)) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToApplySelectedCarrier();
        }

        /** @var \Address|bool $address */
        $address = $this->addressRepository->findOneBy([
            'id_address' => $addressDeliveryId,
        ]);

        if (!$address) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindOrderDeliveryAddress();
        }

        /** @var \Country|bool $country */
        $country = $this->countryRepository->findOneBy([
            'id_country' => $address->id_country,
        ]);

        if (!$country) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindOrderDeliveryCountry();
        }

        /** @var float|bool $deliveryPrice */
        $deliveryPrice = $cart->getPackageShippingCost(
            $subscriptionCarrierId,
            true,
            $country,
            [$subscriptionProduct],
            $this->addressRepository->getZoneById($addressDeliveryId)
        );

        if (is_bool($deliveryPrice) && !$deliveryPrice) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToGetSelectedCarrierPrice();
        }

        return (float) $deliveryPrice;
    }
}
