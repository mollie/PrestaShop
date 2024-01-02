<?php

namespace Mollie\Exception;

use Exception;
use Mollie\Exception\Code\ExceptionCode;

class CouldNotUpdateOrderTotals extends MollieException
{
    public static function failedToUpdateOrderTotals(Exception $exception): self
    {
        return new self(
            'Failed to update order totals.',
            ExceptionCode::ORDER_FAILED_TO_UPDATE_ORDER_TOTALS,
            $exception
        );
    }
}
