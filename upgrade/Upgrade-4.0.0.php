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
 */
function upgrade_module_4_0_0(Mollie $module)
{
	$sql = [];
	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method` (
				`id_payment_method`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`  VARCHAR(64) NOT NULL,
				`method_name`  VARCHAR(64) NOT NULL,
				`enabled`       TINYINT(1),
				`title`      VARCHAR(64),
				`method` VARCHAR(64),
				`description` VARCHAR(255),
				`is_countries_applicable` TINYINT(1),
				`minimal_order_value` decimal(20,6),
				`max_order_value` decimal(20,6),
				`surcharge` INT(10),
				`surcharge_fixed_amount` decimal(20,6),
				`surcharge_percentage` decimal(20,6),
				`surcharge_limit` decimal(20,6),
				`images_json` TEXT
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_issuer` (
				`id_payment_method_issuer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_payment_method` INT(64) NOT NULL,
				`issuers_json` TEXT NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_order_fee` (
				`id_mol_order_fee`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_cart` INT(64) NOT NULL,
				`order_fee` decimal(20,6) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_carrier_information` (
				`id_mol_carrier_information`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_carrier` INT(64) NOT NULL,
				`url_source` VARCHAR(64) NOT NULL,
				`custom_url` VARCHAR(255)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	foreach ($sql as $query) {
		if (false == Db::getInstance()->execute($query)) {
			return false;
		}
	}

	return true;
}
