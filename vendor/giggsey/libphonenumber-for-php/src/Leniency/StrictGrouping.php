<?php

namespace MolliePrefix\libphonenumber\Leniency;

use MolliePrefix\libphonenumber\PhoneNumber;
use MolliePrefix\libphonenumber\PhoneNumberMatcher;
use MolliePrefix\libphonenumber\PhoneNumberUtil;
class StrictGrouping extends \MolliePrefix\libphonenumber\Leniency\AbstractLeniency
{
    protected static $level = 3;
    /**
     * Phone numbers accepted are PhoneNumberUtil::isValidNumber() and are grouped
     * in a possible way for this locale. For example, a US number written as
     * "65 02 53 00 00" and "650253 0000" are not accepted at this leniency level, whereas
     * "650 253 0000", "650 2530000" or "6502530000" are.
     * Numbers with more than one '/' symbol in the national significant number are also dropped at
     * this level.
     *
     * Warning: This level might result in lower coverage especially for regions outside of country
     * code "+1". If you are not sure about which level to use, email the discussion group
     * libphonenumber-discuss@googlegroups.com.
     *
     * @param PhoneNumber $number
     * @param string $candidate
     * @param PhoneNumberUtil $util
     * @return bool
     */
    public static function verify(\MolliePrefix\libphonenumber\PhoneNumber $number, $candidate, \MolliePrefix\libphonenumber\PhoneNumberUtil $util)
    {
        if (!$util->isValidNumber($number) || !\MolliePrefix\libphonenumber\PhoneNumberMatcher::containsOnlyValidXChars($number, $candidate, $util) || \MolliePrefix\libphonenumber\PhoneNumberMatcher::containsMoreThanOneSlashInNationalNumber($number, $candidate) || !\MolliePrefix\libphonenumber\PhoneNumberMatcher::isNationalPrefixPresentIfRequired($number, $util)) {
            return \false;
        }
        return \MolliePrefix\libphonenumber\PhoneNumberMatcher::checkNumberGroupingIsValid($number, $candidate, $util, function (\MolliePrefix\libphonenumber\PhoneNumberUtil $util, \MolliePrefix\libphonenumber\PhoneNumber $number, $normalizedCandidate, $expectedNumberGroups) {
            return \MolliePrefix\libphonenumber\PhoneNumberMatcher::allNumberGroupsRemainGrouped($util, $number, $normalizedCandidate, $expectedNumberGroups);
        });
    }
}
