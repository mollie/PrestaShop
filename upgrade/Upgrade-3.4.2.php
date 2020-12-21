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
 */
if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @return bool
 *
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgrade_module_3_4_2()
{
	Configuration::updateGlobalValue(
		Mollie\Config\Config::MOLLIE_STATUS_OPEN,
		Configuration::get('PS_OS_BANKWIRE')
	);
	Configuration::updateGlobalValue(
		Mollie\Config\Config::MOLLIE_STATUS_PAID,
		Configuration::get('PS_OS_PAYMENT')
	);
	Configuration::updateGlobalValue(
		Mollie\Config\Config::MOLLIE_STATUS_CANCELED,
		Configuration::get('PS_OS_CANCELED')
	);
	Configuration::updateGlobalValue(
		Mollie\Config\Config::MOLLIE_STATUS_EXPIRED,
		Configuration::get('PS_OS_CANCELED')
	);
	Configuration::updateGlobalValue(
		Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND,
		Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_PARTIAL_REFUND)
	);
	Configuration::updateGlobalValue(Mollie\Config\Config::MOLLIE_STATUS_REFUNDED,
		Configuration::get('PS_OS_REFUND')
	);

	return true;
}
