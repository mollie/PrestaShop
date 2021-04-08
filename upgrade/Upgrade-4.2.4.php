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

use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_2_4($module)
{
	$module->registerHook('actionObjectCurrencyUpdateAfter');
	Configuration::updateValue(
		Config::MOLLIE_STATUS_OPEN,
		Configuration::get(Config::MOLLIE_STATUS_AWAITING)
	);

	return true;
}
