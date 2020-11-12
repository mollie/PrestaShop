<?php

namespace Mollie\Provider;

use Address;

final class PhoneNumberProvider implements PhoneNumberProviderInterface
{
    public function getFromAddress(Address $address)
    {
        $phoneNumber = $this->getMobileOrPhone($address);

        $phoneNumber = str_replace(" ", "", $phoneNumber);
        $phoneNumber = str_replace("+", "", $phoneNumber);

        while ($phoneNumber[0] === "0") {
            $phoneNumber = substr($phoneNumber, 1);
        }

        if ($phoneNumber[0] !== "+") {
            $phoneNumber = "+" . $phoneNumber;
        }

        $regex = "/^\+\d{3,18}$/";
        if (preg_match($regex, $phoneNumber) == 1) {
            return $phoneNumber;
        } else {
            return null;
        }
    }

    private function getMobileOrPhone(Address $address)
    {
        return $address->phone_mobile ?: $address->phone;
    }
}
