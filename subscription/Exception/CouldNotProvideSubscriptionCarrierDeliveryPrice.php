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

namespace Mollie\Subscription\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CouldNotProvideSubscriptionCarrierDeliveryPrice extends MollieSubscriptionException
{
    public static function failedToFindSelectedCarrier(int $subscriptionCarrierId): self
    {
        return new self(
            sprintf(
                'Failed to find selected carrier. Subscription carrier ID: (%s)',
                $subscriptionCarrierId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_SELECTED_CARRIER
        );
    }

    public static function failedToFindCart(int $cartId): self
    {
        return new self(
            sprintf(
                'Failed to find cart. Cart ID: (%s)',
                $cartId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_CART
        );
    }

    public static function failedToFindCustomer(int $customerId): self
    {
        return new self(
            sprintf(
                'Failed to find customer. Customer ID: (%s)',
                $customerId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_CUSTOMER
        );
    }

    public static function failedToApplySelectedCarrier(int $subscriptionCarrierId): self
    {
        return new self(
            sprintf(
                'Failed to apply selected carrier. Subscription carrier ID: (%s)',
                $subscriptionCarrierId
            ),
            ExceptionCode::ORDER_FAILED_TO_APPLY_SELECTED_CARRIER
        );
    }

    public static function failedToFindDeliveryAddress(int $deliveryAddressId): self
    {
        return new self(
            sprintf(
                'Failed to find delivery address. Delivery address ID: (%s)',
                $deliveryAddressId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_DELIVERY_ADDRESS
        );
    }

    public static function failedToFindDeliveryCountry(int $countryId): self
    {
        return new self(
            sprintf(
                'Failed to find delivery country. Country ID: (%s)',
                $countryId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_DELIVERY_COUNTRY
        );
    }

    public static function failedToGetSelectedCarrierPrice(int $subscriptionCarrierId): self
    {
        return new self(
            sprintf(
                'Failed to get selected carrier price. Subscription carrier ID: (%s)',
                $subscriptionCarrierId
            ),
            ExceptionCode::ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE
        );
    }
}
