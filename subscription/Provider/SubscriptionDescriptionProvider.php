<?php

declare(strict_types=1);

namespace Mollie\Subscription\Provider;

use Order;

class SubscriptionDescriptionProvider
{
    public function getSubscriptionDescription(Order $order)
    {
        return implode('-', [
            'subscription',
            $order->reference,
        ]);
    }
}
