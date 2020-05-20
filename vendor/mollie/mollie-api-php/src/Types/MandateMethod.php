<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Types;

use function in_array;

class MandateMethod
{
    const DIRECTDEBIT = "directdebit";
    const CREDITCARD = "creditcard";
    public static function getForFirstPaymentMethod($firstPaymentMethod)
    {
        if (in_array($firstPaymentMethod, [PaymentMethod::APPLEPAY, PaymentMethod::CREDITCARD])) {
            return static::CREDITCARD;
        }
        return static::DIRECTDEBIT;
    }
}
