<?php

namespace Mollie\Exception;

use Exception;
use Mollie\Exception\Code\ExceptionCode;

class CouldNotUpdateOrderTotals extends MollieException
{
    public static function totalsDoesNotMatch(float $transactionAmount, float $calculatedAmount): CouldNotUpdateOrderTotals
    {
        return new self(
            sprintf(
                'Totals does not match. Transaction amount: (%d). Calculated amount: (%d)',
                $transactionAmount,
                $calculatedAmount
            ),
            ExceptionCode::ORDER_TOTALS_DOES_NOT_MATCH
        );
    }

    public static function failedToUpdateOrderTotals(Exception $exception): CouldNotUpdateOrderTotals
    {
        return new self(
            'Failed to update order totals.',
            ExceptionCode::ORDER_FAILED_TO_UPDATE_ORDER_TOTALS,
            $exception
        );
    }
}
