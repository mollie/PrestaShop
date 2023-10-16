<?php

namespace Mollie\Subscription\Exception;

class CouldNotHandleRecurringOrder extends MollieSubscriptionException
{
    public static function failedToFindSelectedCarrier(): self
    {
        return new self(
            'Failed to find selected carrier',
            ExceptionCode::RECURRING_ORDER_FAILED_TO_FIND_SELECTED_CARRIER
        );
    }

    public static function failedToApplySelectedCarrier(): self
    {
        return new self(
            'Failed to apply selected carrier',
            ExceptionCode::RECURRING_ORDER_FAILED_TO_APPLY_SELECTED_CARRIER
        );
    }
}
