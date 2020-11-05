<?php

namespace Mollie\Provider;

use Address;
use Mollie\Utility\PhoneNumberUtility;

final class PhoneNumberProvider implements PhoneNumberProviderInterface
{
    public function getFromAddress(Address $address)
    {
        $phoneNumber = $this->getMobileOrPhone($address);

        return PhoneNumberUtility::isInternationalPhoneNumber($phoneNumber) ? $phoneNumber : null;
    }

    private function getMobileOrPhone(Address $address)
    {
        return $address->phone_mobile ?: $address->phone;
    }
}
