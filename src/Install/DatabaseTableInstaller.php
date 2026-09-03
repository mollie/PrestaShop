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

if (!defined('_PS_VERSION_')) {
    exit;
}

final class DatabaseTableInstaller implements InstallerInterface
{
    public function install()
    {
        $commands = $this->getCommands();

        foreach ($commands as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return $this->alterTableCommands();
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
				 INDEX (cart_id, order_reference),
				 INDEX `mollie_payments_status_created` (bank_status, created_at),
				 INDEX `mollie_payments_created_at` (created_at)
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
			    `id_shop` INT(64) DEFAULT 1,
			    `is_manual_capture` TINYINT(1) DEFAULT 0
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_order_payment_fee` (
				`id_mol_order_payment_fee`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_cart` INT(64) NOT NULL,
				`id_order` INT(64) NOT NULL,
				`fee_tax_incl` decimal(20,6) NOT NULL,
				`fee_tax_excl` decimal(20,6) NOT NULL
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

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_logs` (
                `id_mollie_log` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_log` INT(11),
                `id_shop` INT(11),
                `request` TEXT,
                `response` TEXT,
                `context` TEXT,
                `date_add` DATETIME NOT NULL,
                INDEX (`id_log`),
                INDEX (`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_translations` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_method` VARCHAR(64) NOT NULL,
                `id_lang` INT(11),
                `id_shop` INT(11),
                `text` TINYTEXT,
                INDEX (`id_method`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_excluded_customer_groups` (
                `id_payment_method` INT(64) NOT NULL,
                `id_customer_group` INT(64) NOT NULL,
                PRIMARY KEY (`id_payment_method`, `id_customer_group`),
                INDEX (`id_payment_method`),
                INDEX (`id_customer_group`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return $sql;
    }

    /**
     * Independent guarded blocks, one per schema change. They must stay independent: an early
     * return here would silently skip everything appended after it on an existing install, which
     * is what CREATE TABLE IF NOT EXISTS already fails to cover.
     */
    private function alterTableCommands(): bool
    {
        if (!$this->addMandateIdColumn()) {
            return false;
        }

        return $this->addPaymentOverviewIndexes();
    }

    private function addMandateIdColumn(): bool
    {
        if ($this->columnExists('mollie_payments', 'mandate_id')) {
            return true;
        }

        return (bool) Db::getInstance()->execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments`
            ADD COLUMN `mandate_id` VARCHAR(64);
        ');
    }

    /**
     * Backs the payment overview list. It needs both shapes and uses one or the other depending
     * on the filter: created_at alone lets the default view walk the index backwards and stop at
     * the page size, while bank_status first is what turns a status filter into a range instead
     * of a 500k row index walk with a row lookup per entry.
     *
     * ADD INDEX is an online DDL on MySQL 5.6 and MariaDB 10.0 upwards, so it does not block
     * writes on a shop with a large history.
     */
    private function addPaymentOverviewIndexes(): bool
    {
        $indexes = [
            'mollie_payments_status_created' => '`bank_status`, `created_at`',
            'mollie_payments_created_at' => '`created_at`',
        ];

        foreach ($indexes as $name => $columns) {
            if ($this->indexExists('mollie_payments', $name)) {
                continue;
            }

            $added = Db::getInstance()->execute('
                ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments`
                ADD INDEX `' . bqSQL($name) . '` (' . $columns . ');
            ');

            if (!$added) {
                return false;
            }
        }

        return true;
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) Db::getInstance()->getValue('
            SELECT COUNT(*) > 0
            FROM information_schema.columns
            WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '"
                AND TABLE_NAME = "' . _DB_PREFIX_ . pSQL($table) . '"
                AND COLUMN_NAME = "' . pSQL($column) . '";
        ');
    }

    private function indexExists(string $table, string $index): bool
    {
        return (bool) Db::getInstance()->getValue('
            SELECT COUNT(*) > 0
            FROM information_schema.statistics
            WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '"
                AND TABLE_NAME = "' . _DB_PREFIX_ . pSQL($table) . '"
                AND INDEX_NAME = "' . pSQL($index) . '";
        ');
    }
}
