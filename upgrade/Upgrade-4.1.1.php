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
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_1_1($module)
{
	if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
		$module->unregisterHook('displayPaymentEU');
	}

	return true;
}
