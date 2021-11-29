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

namespace Mollie\Utility;

use Customer;
use Validate;

class CustomerUtility
{
    /**
     * @param int $id
     * @return string
     */
    public static function getCustomerFullName($id)
    {
        $customer = new Customer($id);

        if (!Validate::isLoadedObject($customer)) {
            return "";
        }

        return "{$customer->firstname} {$customer->lastname}";
    }
}
