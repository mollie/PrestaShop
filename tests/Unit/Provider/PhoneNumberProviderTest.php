<?php


namespace Provider;

use Address;
use Mollie\Provider\PhoneNumberProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberProviderTest extends TestCase
{
    /**
     * @dataProvider getFromAddressDataProvider
     *
     * @param $phoneNumber
     * @param $result
     */
    public function testGetFromAddress($phoneNumber, $result)
    {
        $address = new Address();
        $address->phone = $phoneNumber;

        $phoneNumberProvider = new PhoneNumberProvider();
        $fixedPhoneNumber = $phoneNumberProvider->getFromAddress($address);

        self::assertEquals($result, $fixedPhoneNumber);
    }

    public function getFromAddressDataProvider()
    {
        return [
            'normal number' => [
                'phoneNumber' => '+37064742671',
                'result' => '+37064742671',
            ],
            'Number without +' => [
                'phoneNumber' => '37064742671',
                'result' => '+37064742671',
            ],
            'normal without + and with spaces' => [
                'phoneNumber' => '370 64 742671',
                'result' => '+37064742671',
            ],
            'number that starts with 86' => [
                'phoneNumber' => '864742671',
                'result' => '+864742671',
            ],
            'number that starts with +0' => [
                'phoneNumber' => '+0164742671',
                'result' => '+164742671',
            ],
            'number that consists of 0s with +' => [
                'phoneNumber' => '+000000',
                'result' => null,
            ],
            'number that consists of 0s' => [
                'phoneNumber' => '000000',
                'result' => null,
            ],
        ];
    }
}
