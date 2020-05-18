<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Types;

class SubscriptionStatus
{
    const STATUS_ACTIVE = "active";
    const STATUS_PENDING = "pending";
    // Waiting for a valid mandate.
    const STATUS_CANCELED = "canceled";
    const STATUS_SUSPENDED = "suspended";
    // Active, but mandate became invalid.
    const STATUS_COMPLETED = "completed";
}
