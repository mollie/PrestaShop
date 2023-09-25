<?php

namespace Mollie\Subscription\Exception;

class CouldNotProvideCarrierDeliveryPrice extends MollieSubscriptionException
{
    public static function failedToFindSelectedCarrierForSubscriptionOrder(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to find selected carrier for subscription order',
            ExceptionCode::ORDER_FAILED_TO_FIND_SELECTED_CARRIER_FOR_SUBSCRIPTION_ORDER
        );
    }

    public static function failedToFindOrderCart(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to find order cart',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_CART
        );
    }

    public static function failedToFindOrderCustomer(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to find order customer',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_CUSTOMER
        );
    }

    public static function failedToApplySelectedCarrierForSubscriptionOrder(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to apply selected carrier for subscription order',
            ExceptionCode::ORDER_FAILED_TO_APPLY_SELECTED_CARRIER_FOR_SUBSCRIPTION_ORDER
        );
    }

    public static function failedToFindOrderDeliveryAddress(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to find order delivery address',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DELIVERY_ADDRESS
        );
    }

    public static function failedToFindOrderDeliveryCountry(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to find order delivery country',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DELIVERY_COUNTRY
        );
    }

    public static function failedToGetSelectedCarrierPriceForSubscriptionOrder(): CouldNotProvideCarrierDeliveryPrice
    {
        return new self(
            'Failed to get selected carrier price for subscription order',
            ExceptionCode::ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE_FOR_SUBSCRIPTION_ORDER
        );
    }
}
