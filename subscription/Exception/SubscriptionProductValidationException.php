<?php

declare(strict_types=1);

namespace Mollie\Subscription\Exception;

class SubscriptionProductValidationException extends MollieSubscriptionException
{
    const MULTTIPLE_PRODUCTS_IN_CART = 0;
}
