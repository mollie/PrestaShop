<?php

namespace MolliePrefix\libphonenumber\Leniency;

use MolliePrefix\libphonenumber\PhoneNumber;
use MolliePrefix\libphonenumber\PhoneNumberUtil;
class Possible extends \MolliePrefix\libphonenumber\Leniency\AbstractLeniency
{
    protected static $level = 1;
    /**
     * Phone numbers accepted are PhoneNumberUtil::isPossibleNumber(), but not necessarily
     * PhoneNumberUtil::isValidNumber().
     *
     * @param PhoneNumber $number
     * @param string $candidate
     * @param PhoneNumberUtil $util
     * @return bool
     */
    public static function verify(\MolliePrefix\libphonenumber\PhoneNumber $number, $candidate, \MolliePrefix\libphonenumber\PhoneNumberUtil $util)
    {
        return $util->isPossibleNumber($number);
    }
}
