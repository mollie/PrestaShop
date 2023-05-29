<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Install;

use Db;

final class DatabaseTableInstaller implements InstallerInterface
{
    public function install()
    {
        $commands = $this->getCommands();

        foreach ($commands as $query) {
            if (false == Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    private function getCommands()
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mollie_payments` (
				`transaction_id`  VARCHAR(64)  NOT NULL PRIMARY KEY,
				`cart_id`         INT(64),
				`order_id`        INT(64),
				`order_reference` VARCHAR(191),
				`mandate_id`      VARCHAR(64),
				`method`          VARCHAR(128) NOT NULL,
				`bank_status`     VARCHAR(64)  NOT NULL,
				`reason`          VARCHAR(64),
				`created_at`      DATETIME     NOT NULL,
				`updated_at`      DATETIME     DEFAULT NULL,
				 INDEX (cart_id, order_reference)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

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
				`surcharge_fixed_amount_tax_excl` decimal(20,6),
				`tax_rules_group_id` INT(10),
				`surcharge_percentage` decimal(20,6),
				`surcharge_limit` decimal(20,6),
				`images_json` TEXT,
				`min_amount` decimal(20,6),
				`max_amount` decimal(20,6),
				`live_environment` TINYINT(1),
				`position` INT(10),
			    `id_shop` INT(64) DEFAULT 1
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

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart` (
                `id_mol_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `order_id` INT(64) NOT NULL,
                `cart_id` INT(64) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_customer` (
                        `id_mol_customer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
                        `customer_id` VARCHAR(64) NOT NULL,
                        `name` VARCHAR(64) NOT NULL,
                        `email` VARCHAR(64) NOT NULL,
                        `created_at` VARCHAR(64) NOT NULL
                    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule` (
                `id_mol_pending_order_cart_rule` INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `id_order` VARCHAR(64) NOT NULL,
                `id_cart_rule` VARCHAR(64) NOT NULL,
                `name` VARCHAR(64) NOT NULL,
                `value_tax_incl` decimal(20,6) NOT NULL,
                `value_tax_excl` decimal(20,6) NOT NULL,
                `free_shipping` TINYINT(1) NOT NULL,
                `id_order_invoice` INT(64) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        return $sql;
    }
}
