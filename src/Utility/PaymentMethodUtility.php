<?php

use Mollie\Config\Config;

class PaymentMethodUtility
{
    public static function getPaymentMethodName($method)
    {
        return array_key_exists($method, Config::$methods) ? Config::$methods[$method] : $method;
    }
}
