<?php

namespace MolliePrefix\libphonenumber\Leniency;

use MolliePrefix\libphonenumber\PhoneNumber;
use MolliePrefix\libphonenumber\PhoneNumberMatcher;
use MolliePrefix\libphonenumber\PhoneNumberUtil;
class Valid extends \MolliePrefix\libphonenumber\Leniency\AbstractLeniency
{
    protected static $level = 2;
    /**
     * Phone numbers accepted are PhoneNumberUtil::isPossibleNumber() and PhoneNumberUtil::isValidNumber().
     * Numbers written in national format must have their national-prefix present if it is usually written
     * for a number of this type.
     *
     * @param PhoneNumber $number
     * @param string $candidate
     * @param PhoneNumberUtil $util
     * @return bool
     */
    public static function verify(\MolliePrefix\libphonenumber\PhoneNumber $number, $candidate, \MolliePrefix\libphonenumber\PhoneNumberUtil $util)
    {
        if (!$util->isValidNumber($number) || !\MolliePrefix\libphonenumber\PhoneNumberMatcher::containsOnlyValidXChars($number, $candidate, $util)) {
            return \false;
        }
        return \MolliePrefix\libphonenumber\PhoneNumberMatcher::isNationalPrefixPresentIfRequired($number, $util);
    }
}
