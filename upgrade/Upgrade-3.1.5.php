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
function upgrade_module_3_1_5()
{
	foreach (Shop::getShops(false, null, true) as $shop) {
		Configuration::updateValue(
			'MOLLIE_STATUS_CANCELED',
			Configuration::get('MOLLIE_STATUS_CANCELLED', null, (int) $shop['id_shop_group'], (int) $shop['id_shop']),
			false,
			(int) $shop['id_shop_group'],
			(int) $shop['id_shop']
		);
	}
	Configuration::updateGlobalValue('MOLLIE_STATUS_CANCELED', Configuration::get('MOLLIE_STATUS_CANCELLED'));
	Configuration::deleteByName('MOLLIE_STATUS_CANCELLED');

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
