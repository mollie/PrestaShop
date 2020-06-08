<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Types;

class MandateMethod
{
    const DIRECTDEBIT = "directdebit";
    const CREDITCARD = "creditcard";
    public static function getForFirstPaymentMethod($firstPaymentMethod)
    {
        if (\in_array($firstPaymentMethod, [\_PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::APPLEPAY, \_PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod::CREDITCARD])) {
            return static::CREDITCARD;
        }
        return static::DIRECTDEBIT;
    }
}
