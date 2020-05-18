<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Types;

class MandateMethod
{
    const DIRECTDEBIT = "directdebit";
    const CREDITCARD = "creditcard";
    public static function getForFirstPaymentMethod($firstPaymentMethod)
    {
        if (\in_array($firstPaymentMethod, [\_PhpScoper5ea00cc67502b\Mollie\Api\Types\PaymentMethod::APPLEPAY, \_PhpScoper5ea00cc67502b\Mollie\Api\Types\PaymentMethod::CREDITCARD])) {
            return static::CREDITCARD;
        }
        return static::DIRECTDEBIT;
    }
}
