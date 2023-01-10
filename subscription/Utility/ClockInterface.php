<?php

namespace Mollie\Subscription\Utility;

interface ClockInterface
{
    public function getCurrentDate(string $format = 'Y-m-d H:i:s'): string;
}
