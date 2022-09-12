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

interface StreetAndNumberProviderInterface
{
    /**
     * @return string
     */
    public function getStreetAndNumberFromAddress(Address $address);
}
