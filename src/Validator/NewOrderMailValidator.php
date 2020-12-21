<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Validator;

use Configuration;
use Mollie\Config\Config;

class NewOrderMailValidator implements MailValidatorInterface
{
	/**
	 * @param int $orderState
	 *
	 * @return bool
	 */
	public function validate($orderState)
	{
		switch (Configuration::get(Config::MOLLIE_SEND_NEW_ORDER)) {
			case Config::NEW_ORDER_MAIL_SEND_ON_CREATION:
				return true;
			case Config::NEW_ORDER_MAIL_SEND_ON_PAID:
				return $this->validateOrderState($orderState);
			case Config::NEW_ORDER_MAIL_SEND_ON_NEVER:
			default:
				return false;
		}
	}

	/**
	 * @param int $orderState
	 *
	 * @return bool
	 */
	private function validateOrderState($orderState)
	{
		return (int) Configuration::get(Config::MOLLIE_STATUS_PAID) === $orderState ||
			(int) Configuration::get(Config::STATUS_PS_OS_OUTOFSTOCK_PAID) === $orderState;
	}
}
