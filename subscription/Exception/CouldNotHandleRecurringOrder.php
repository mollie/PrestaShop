<?php

namespace Mollie\Subscription\Exception;

use Exception;

class CouldNotHandleRecurringOrder extends MollieSubscriptionException
{
    public static function failedToCreateOrderPaymentFee(Exception $exception): CouldNotHandleRecurringOrder
    {
        return new self(
            'Failed to create order payment fee',
            ExceptionCode::ORDER_FAILED_TO_CREATE_ORDER_PAYMENT_FEE,
            $exception
        );
    }

    public static function failedToUpdateOrderTotalWithPaymentFee(Exception $exception): CouldNotHandleRecurringOrder
    {
        return new self(
            'Failed to update order total with payment fee.',
            ExceptionCode::ORDER_FAILED_TO_UPDATE_ORDER_TOTAL_WITH_PAYMENT_FEE,
            $exception
        );
    }
}
