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

namespace Mollie\Validator;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;

class NewOrderMailValidator implements MailValidatorInterface
{
	/**
	 * @var ConfigurationAdapter
	 */
	private $configurationAdapter;

	public function __construct(ConfigurationAdapter $configurationAdapter)
	{
		$this->configurationAdapter = $configurationAdapter;
	}

	/**
	 * @param int $orderStateId
	 *
	 * @return bool
	 */
	public function validate($orderStateId)
	{
		switch ($this->configurationAdapter->get(Config::MOLLIE_SEND_NEW_ORDER)) {
			case Config::NEW_ORDER_MAIL_SEND_ON_CREATION:
				return true;
			case Config::NEW_ORDER_MAIL_SEND_ON_PAID:
				return $this->validateOrderState($orderStateId);
			case Config::NEW_ORDER_MAIL_SEND_ON_NEVER:
			default:
				return false;
		}
	}

	/**
	 * @param int $orderStateId
	 *
	 * @return bool
	 */
	private function validateOrderState($orderStateId)
	{
		if ((int) $this->configurationAdapter->get(Config::MOLLIE_STATUS_PAID) === $orderStateId) {
			return true;
		}

		if ((int) $this->configurationAdapter->get(Config::STATUS_PS_OS_OUTOFSTOCK_PAID) === $orderStateId) {
			return true;
		}

		return false;
	}
}
