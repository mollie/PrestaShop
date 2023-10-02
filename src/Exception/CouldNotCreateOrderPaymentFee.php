<?php

namespace Mollie\Exception;

use Exception;
use Mollie\Exception\Code\ExceptionCode;

class CouldNotCreateOrderPaymentFee extends MollieException
{
    public static function failedToInsertOrderPaymentFee(Exception $exception): self
    {
        return new self(
            'Failed to insert order payment fee.',
            ExceptionCode::ORDER_FAILED_TO_INSERT_ORDER_PAYMENT_FEE,
            $exception
        );
    }
}
