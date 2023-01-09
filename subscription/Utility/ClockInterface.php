<?php

namespace Mollie\Subscription\Utility;

interface ClockInterface
{
    public function getCurrentDate(string $format): string;
}
