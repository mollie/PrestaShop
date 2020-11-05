<?php

namespace Mollie\Utility;

class PhoneNumberUtility
{
    /**
     * Simple and naive implementation of checking if phone number is international by just checking its sign
     *
     * @param $phoneNumber
     *
     * @return bool
     */
    public static function isInternationalPhoneNumber($phoneNumber)
    {
        return strpos($phoneNumber, '+') === 0;
    }
}
