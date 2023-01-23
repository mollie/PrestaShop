<?php

declare(strict_types=1);

namespace Mollie\Subscription\Provider;

use Mollie\Subscription\Config\Config;
use Order;

class SubscriptionDescriptionProvider
{
    public function getSubscriptionDescription(Order $order, string $currencyIsoCode)
    {
        return implode('-', [
            Config::DESCRIPTION_PREFIX,
            $order->id,
            $order->total_paid_tax_incl,
            $currencyIsoCode,
        ]);
    }
}
