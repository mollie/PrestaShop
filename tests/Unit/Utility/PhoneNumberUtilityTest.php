<?php

use Mollie\Exception\PhoneNumberParseException;
use Mollie\Utility\PhoneNumberUtility;
use MolliePrefix\libphonenumber\NumberParseException;
use PHPUnit\Framework\TestCase;

if (!class_exists('Validate')) {
    /**
     * @todo: these hacks should be removed once our tests are running in prestashop environment in pipelines.
     */
    class Validate {
        public static function isLanguageIsoCode($iso_code)
        {
            return preg_match('/^[a-zA-Z]{2,3}$/', $iso_code);
        }
    }
}


class PhoneNumberUtilityTest extends TestCase
{
    /**
     * @dataProvider provideFormatNumber
     */
    public function testItFormatsInternationalPhoneNumberCorrectly($number, $countryCode, $expectedNumber)
    {
        $result = PhoneNumberUtility::internationalizeNumber($number, $countryCode);

        $this->assertEquals($expectedNumber, $result);
    }

    public function provideFormatNumber()
    {
        return [
            'Lithuania without country code' => [
                '862816785',
                'lt',
                '+370 628 16785'
            ],
            'Netherlands with country code already entered' => [
                '+31203698545',
                'NL',
                '+31 20 369 8545'
            ],
            'UK London region phone number without country code and have wrapping ()' => [
                '(020) 1234 5678',
                'GB',
                '+44 20 1234 5678'
            ],
            'Adding gaps' => [
                '+37062816785',
                'LT',
                '+370 628 16785'
            ]
        ];
    }

    /**
     * @dataProvider providePhoneNumberErrors
     */
    public function testInternationalizePhoneNumber_errors($number, $countryCode, $exceptionCode)
    {
        $this->expectException(PhoneNumberParseException::class);
        $this->expectExceptionCode($exceptionCode);

        PhoneNumberUtility::internationalizeNumber($number, $countryCode);
    }

    public function providePhoneNumberErrors()
    {
        return [
            'no number provided' => [
                '',
                'LT',
                NumberParseException::NOT_A_NUMBER
            ],
            'wrong country code' => [
                '+37062816785',
                'MIDDLEEARTH',
                NumberParseException::INVALID_COUNTRY_CODE
            ],
            'too short number' => [
                '1',
                'LT',
                NumberParseException::NOT_A_NUMBER
            ]
        ];
    }
}
