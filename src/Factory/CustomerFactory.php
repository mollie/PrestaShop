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

namespace Mollie\Factory;

use Customer;
use Mollie\Utility\ContextUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerFactory
{
    public function recreateFromRequest($customerId, $customerSecureKey, $context)
    {
        if ($customerId) {
            $customer = new Customer($customerId);
            if ($customer->secure_key === $customerSecureKey) {
                return ContextUtility::setCustomerToContext($context, $customer);
            }
        }

        return $context;
    }
}
