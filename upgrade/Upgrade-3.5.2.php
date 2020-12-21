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
function upgrade_module_3_5_2()
{
	$trackingConfigId = Configuration::getIdByName(Mollie\Config\Config::MOLLIE_TRACKING_URLS);

	$query = 'DELETE FROM`' . _DB_PREFIX_ . 'configuration_lang` 
            WHERE id_configuration = "' . pSQL($trackingConfigId) . '"';

	if (!Db::getInstance()->execute($query)) {
		return false;
	}

	return true;
}
