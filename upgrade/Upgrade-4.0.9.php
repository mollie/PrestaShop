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
function upgrade_module_4_0_9($module)
{
	$module->registerHook('actionAdminStatusesListingFieldsModifier');

	return true;
}
