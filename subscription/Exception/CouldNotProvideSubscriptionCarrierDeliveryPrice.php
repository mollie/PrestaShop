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

    public static function failedToFindOrderCart(int $cartId): self
    {
        return new self(
            sprintf(
                'Failed to find order cart. Cart ID: (%s)',
                $cartId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_CART
        );
    }

    public static function failedToFindOrderCustomer(int $customerId): self
    {
        return new self(
            sprintf(
                'Failed to find order customer. Customer ID: (%s)',
                $customerId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_CUSTOMER
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

    public static function failedToFindOrderDeliveryAddress(int $deliveryAddressId): self
    {
        return new self(
            sprintf(
                'Failed to find order delivery address. Delivery address ID: (%s)',
                $deliveryAddressId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DELIVERY_ADDRESS
        );
    }

    public static function failedToFindOrderDeliveryCountry(int $countryId): self
    {
        return new self(
            sprintf(
                'Failed to find order delivery country. Country ID: (%s)',
                $countryId
            ),
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DELIVERY_COUNTRY
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
