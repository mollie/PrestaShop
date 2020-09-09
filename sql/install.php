<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mollie_payments` (
				`transaction_id`  VARCHAR(64)  NOT NULL PRIMARY KEY,
				`cart_id`         INT(64),
				`order_id`        INT(64),
				`order_reference` VARCHAR(191),
				`method`          VARCHAR(128) NOT NULL,
				`bank_status`     VARCHAR(64)  NOT NULL,
				`created_at`      DATETIME     NOT NULL,
				`updated_at`      DATETIME     DEFAULT NULL,
				 INDEX (cart_id, order_reference)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_payment_method` (
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
				`images_json` TEXT,
				`live_environment` TINYINT(1)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_payment_method_issuer` (
				`id_payment_method_issuer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_payment_method` INT(64) NOT NULL,
				`issuers_json` TEXT NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_order_fee` (
				`id_mol_order_fee`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_cart` INT(64) NOT NULL,
				`order_fee` decimal(20,6) NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_carrier_information` (
				`id_mol_carrier_information`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_carrier` INT(64) NOT NULL,
				`url_source` VARCHAR(64) NOT NULL,
				`custom_url` VARCHAR(255)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_excluded_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = '
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_pending_order_cart` (
				`id_mol_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`order_id` INT(64) NOT NULL,
				`cart_id` INT(64) NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
';

$sql[] = '
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mol_customer` (
				`id_mol_customer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`customer_id` VARCHAR(64) NOT NULL,
				`name` VARCHAR(64) NOT NULL,
				`email` VARCHAR(64) NOT NULL,
				`created_at` VARCHAR(64) NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
