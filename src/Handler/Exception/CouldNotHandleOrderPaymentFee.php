<?php

namespace Mollie\Handler\Exception;

use Exception;
use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\MollieException;

class CouldNotHandleOrderPaymentFee extends MollieException
{
    public static function failedToRetrievePaymentMethod(Exception $exception): CouldNotHandleOrderPaymentFee
    {
        return new self(
            'Failed to retrieve payment method',
            ExceptionCode::ORDER_FAILED_TO_RETRIEVE_PAYMENT_METHOD,
            $exception
        );
    }

    public static function failedToRetrievePaymentFee(Exception $exception): CouldNotHandleOrderPaymentFee
    {
        return new self(
            'Failed to retrieve payment fee',
            ExceptionCode::ORDER_FAILED_TO_RETRIEVE_PAYMENT_FEE,
            $exception
        );
    }

    public static function failedToCreateOrderPaymentFee(Exception $exception): CouldNotHandleOrderPaymentFee
    {
        return new self(
            'Failed to create order payment fee',
            ExceptionCode::ORDER_FAILED_TO_CREATE_ORDER_PAYMENT_FEE,
            $exception
        );
    }

    public static function failedToUpdateOrderTotalWithPaymentFee(Exception $exception): CouldNotHandleOrderPaymentFee
    {
        return new self(
            'Failed to update order total with payment fee.',
            ExceptionCode::ORDER_FAILED_TO_UPDATE_ORDER_TOTAL_WITH_PAYMENT_FEE,
            $exception
        );
    }
}
