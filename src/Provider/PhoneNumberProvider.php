<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Provider;

use Address;
use AddressFormat;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class PhoneNumberProvider implements PhoneNumberProviderInterface
{
    public function getFromAddress(Address $address)
    {
        $phoneNumber = $this->getMobileOrPhone($address);

        $phoneNumber = str_replace(' ', '', $phoneNumber);

        if (empty($phoneNumber)) {
            return null;
        }

        // If the phone number starts with a '+', validate that it's in E.164 format
        if ($phoneNumber[0] === '+') {
            // E.164 format: +<country_code><number> and should be between 3 and 18 digits total
            if (preg_match("/^\+\d{3,18}$/", $phoneNumber)) {
                return $phoneNumber; // Return the number if it matches E.164 format
            } else {
                return null;
            }
        }

        // If the phone number starts with '0' (a local number), it's considered invalid in E.164 format
        if ($phoneNumber[0] === '0') {
            return null;
        }

        return null;
    }

    private function getMobileOrPhone(Address $address)
    {
        // Retrieve either the mobile or regular phone number based on the address format
        $addressFormat = new AddressFormat((int) $address->id_country);

        if (strpos($addressFormat->format, 'phone_mobile') !== false) {
            return $address->phone_mobile ?: $address->phone;
        }

        return $address->phone;
    }
}
