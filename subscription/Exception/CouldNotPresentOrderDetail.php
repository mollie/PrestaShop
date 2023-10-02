<?php

namespace Mollie\Subscription\Exception;

class CouldNotPresentOrderDetail extends MollieSubscriptionException
{
    public static function failedToFindOrder(): CouldNotPresentOrderDetail
    {
        return new self(
            'Failed to find order',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER
        );
    }

    public static function failedToFindOrderDetail(): CouldNotPresentOrderDetail
    {
        return new self(
            'Failed to find order detail',
            ExceptionCode::ORDER_FAILED_TO_FIND_ORDER_DETAIL
        );
    }

    public static function failedToFindProduct(): CouldNotPresentOrderDetail
    {
        return new self(
            'Failed to find product',
            ExceptionCode::ORDER_FAILED_TO_FIND_PRODUCT
        );
    }

    public static function failedToFindCurrency(): CouldNotPresentOrderDetail
    {
        return new self(
            'Failed to find currency',
            ExceptionCode::ORDER_FAILED_TO_FIND_CURRENCY
        );
    }
}
