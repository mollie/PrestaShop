<?php

use Mollie\Utility\PhoneNumberUtility;
use PHPUnit\Framework\TestCase;



class PhoneNumberUtilityTest extends TestCase
{
    /**
     * @dataProvider providePhoneNumber
     */
    public function testIsInternationPhoneNumber($phoneNumber, $expected)
    {
        $actual = PhoneNumberUtility::isInternationalPhoneNumber($phoneNumber);
        $this->assertEquals($expected, $actual);
    }

    public function providePhoneNumber()
    {
        return [
            [
                '+370615222405',
                true
            ],
            [
                '861522405',
                false
            ],
            [
                '+1', // cant be but for now lets keep it as is.
                true
            ]
        ];
    }
}
