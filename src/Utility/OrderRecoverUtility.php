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

class OrderRecoverUtility
{
    public static function recoverCreatedOrder($context, int $customerId)
    {
        $customer = new Customer($customerId);
        $customer->logged = true;
        $context->customer = new Customer($customerId);
        $context->cookie->id_customer = (int) $customerId;
        $context->customer = $customer;
        $context->cookie->id_customer = (int) $customer->id;
        $context->cookie->customer_lastname = $customer->lastname;
        $context->cookie->customer_firstname = $customer->firstname;
        $context->cookie->logged = 1;
        $context->cookie->check_cgv = 1;
        $context->cookie->is_guest = $customer->isGuest();
        $context->cookie->passwd = $customer->passwd;
        $context->cookie->email = $customer->email;
    }
}
