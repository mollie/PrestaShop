<?php

declare(strict_types=1);

namespace Mollie\Subscription\Utility;

class Clock implements ClockInterface
{
    public function getCurrentDate(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}
