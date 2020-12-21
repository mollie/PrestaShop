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
 *
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgrade_module_3_3_0($module)
{
	try {
		if (!Db::getInstance()->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\'
                AND TABLE_NAME = \'' . _DB_PREFIX_ . 'mollie_payments\'
                AND COLUMN_NAME = \'order_reference\'')) {
			Db::getInstance()->execute(
				'ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments` ADD `order_reference` varchar(191)'
			);
		}
	} catch (PrestaShopException $e) {
		PrestaShopLogger::addLog("Mollie update error: {$e->getMessage()}");
	}

	if (method_exists($module, 'setDefaultCarrierStatuses')) {
		$module->setDefaultCarrierStatuses();
	}

	Configuration::updateValue('MOLLIE_API', 'payments');

	// Major changes, need to clear the cache
	if (!Mollie::$cacheCleared) {
		if (method_exists('Tools', 'clearAllCache')) {
			Tools::clearAllCache();
		}
		if (method_exists('Tools', 'clearCache')) {
			Tools::clearCache();
		}
		Mollie::$cacheCleared = true;
	}

	return true;
}
