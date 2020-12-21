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
function upgrade_module_3_4_3()
{
	$query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`         VARCHAR(64),
				`id_country`        INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	if (!Db::getInstance()->execute($query)) {
		return false;
	}

	return true;
}
