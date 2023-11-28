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

use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Repository\CarrierRepositoryInterface;
use Mollie\Repository\CartRepositoryInterface;
use Mollie\Repository\CountryRepositoryInterface;
use Mollie\Repository\CustomerRepositoryInterface;
use Mollie\Subscription\DTO\SubscriptionCarrierDeliveryPriceData;
use Mollie\Subscription\Exception\CouldNotProvideSubscriptionCarrierDeliveryPrice;

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
     * @throws CouldNotProvideSubscriptionCarrierDeliveryPrice
     */
    public function getPrice(SubscriptionCarrierDeliveryPriceData $data): float
    {
        /** @var \Carrier|null $carrier */
        $carrier = $this->carrierRepository->findOneBy([
            'id_carrier' => $data->getSubscriptionCarrierId(),
            'active' => 1,
            'deleted' => 0,
        ]);

        if (!$carrier) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindSelectedCarrier($data->getSubscriptionCarrierId());
        }

        /** @var \Cart|null $cart */
        $cart = $this->cartRepository->findOneBy([
            'id_cart' => $data->getCartId(),
        ]);

        if (!$cart) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindCart($data->getCartId());
        }

        /** @var \Customer|null $customer */
        $customer = $this->customerRepository->findOneBy([
            'id_customer' => $data->getCustomerId(),
        ]);

        if (!$customer) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindCustomer($data->getCustomerId());
        }

        $getAvailableOrderCarriers = $this->carrierRepository->getCarriersForOrder(
            $this->addressRepository->getZoneById($data->getDeliveryAddressId()),
            $customer->getGroups(),
            $cart
        );

        if (!in_array($data->getSubscriptionCarrierId(), array_column($getAvailableOrderCarriers, 'id_carrier'), false)) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToApplySelectedCarrier($data->getSubscriptionCarrierId());
        }

        /** @var \Address|bool $address */
        $address = $this->addressRepository->findOneBy([
            'id_address' => $data->getDeliveryAddressId(),
        ]);

        if (!$address) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindDeliveryAddress($data->getDeliveryAddressId());
        }

        /** @var \Country|bool $country */
        $country = $this->countryRepository->findOneBy([
            'id_country' => $address->id_country,
        ]);

        if (!$country) {
            throw CouldNotProvideSubscriptionCarrierDeliveryPrice::failedToFindDeliveryCountry((int) $address->id_country);
        }

        /** @var float|bool $deliveryPrice */
        $deliveryPrice = $cart->getPackageShippingCost(
            $data->getSubscriptionCarrierId(),
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
