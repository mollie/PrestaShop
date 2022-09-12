<?php
/**
 *
 *   Do not copy, modify or distribute this document in any form.
 *
 *   @author     Vitaly <vitaly@blauwfruit.nl>
 *   @copyright  Copyright (c) 2013-2022 blauwfruit (http://blauwfruit.nl)
 *   @license    Proprietary Software
 *
 */

namespace Mollie\Provider;

use Address;

final class StreetAndNumberProvider implements StreetAndNumberProviderInterface
{
    public function getStreetAndNumberFromAddress(Address $address)
    {
        $street_and_number = $address->address1;
        if($number = trim($address->address2)){
            $street_and_number .= ' ' . $number;
        }

        return $street_and_number;
    }
}
