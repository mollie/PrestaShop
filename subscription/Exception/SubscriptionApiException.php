<?php

declare(strict_types=1);

namespace Mollie\Subscription\Exception;

class SubscriptionApiException extends MollieSubscriptionException
{
    public const CREATION_FAILED = 0;

    public const CANCELLATION_FAILED = 10;

    public const GETTER_FAILED = 20;

    public const UPDATE_FAILED = 30;
}
