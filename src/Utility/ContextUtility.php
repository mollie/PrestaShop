<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Context;
use Customer;

class ContextUtility
{
	public static function setCustomerToContext(Context $context, Customer $customer)
	{
		$context->customer = $customer;
		$context->cookie->__set('id_customer', (int) $customer->id);
		$context->cookie->__set('customer_lastname', $customer->lastname);
		$context->cookie->__set('customer_firstname', $customer->firstname);
		$context->cookie->__set('logged', 1);
		$context->cookie->__set('check_cgv', 1);
		$context->cookie->__set('is_guest', $customer->isGuest());
		$context->cookie->__set('passwd', $customer->passwd);
		$context->cookie->__set('email', $customer->email);

		return $context;
	}
}
