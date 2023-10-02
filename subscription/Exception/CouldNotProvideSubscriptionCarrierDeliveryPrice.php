<?php

namespace Mollie\Subscription\Exception;

class CouldNotProvideSubscriptionCarrierDeliveryPrice extends MollieSubscriptionException
{
    public static function failedToFindSelectedCarrier(): self
    {
        return new self(
            'Failed to find selected carrier',
            ExceptionCode::ORDER_FAILED_TO_FIND_SELECTED_CARRIER
        );
    }

    public static function failedToFindOrderCart(): self
    {
        return new self(
            'Failed to find order cart',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_CART
        );
    }

    public static function failedToFindOrderCustomer(): self
    {
        return new self(
            'Failed to find order customer',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_CUSTOMER
        );
    }

    public static function failedToApplySelectedCarrier(): self
    {
        return new self(
            'Failed to apply selected carrier',
            ExceptionCode::ORDER_FAILED_TO_APPLY_SELECTED_CARRIER
        );
    }

    public static function failedToFindOrderDeliveryAddress(): self
    {
        return new self(
            'Failed to find order delivery address',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DELIVERY_ADDRESS
        );
    }

    public static function failedToFindOrderDeliveryCountry(): self
    {
        return new self(
            'Failed to find order delivery country',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DELIVERY_COUNTRY
        );
    }

    public static function failedToGetSelectedCarrierPrice(): self
    {
        return new self(
            'Failed to get selected carrier price',
            ExceptionCode::ORDER_FAILED_TO_GET_SELECTED_CARRIER_PRICE
        );
    }
}
