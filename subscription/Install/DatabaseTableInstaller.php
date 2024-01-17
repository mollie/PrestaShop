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

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use Db;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class DatabaseTableInstaller extends AbstractInstaller
{
    public function install(): bool
    {
        $commands = $this->getCommands();

        foreach ($commands as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return $this->alterTableCommands();
    }

    private function getCommands(): array
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_recurring_order` (
				`id_mol_recurring_order`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_order` INT(64) NOT NULL,
				`id_cart` INT(64) NOT NULL,
				`id_currency` INT(64) NOT NULL,
				`id_customer` INT(64) NOT NULL,
				`id_address_delivery` INT(64) NOT NULL,
				`id_address_invoice` INT(64) NOT NULL,
				`description` VARCHAR(64) NOT NULL,
				`status` VARCHAR(64) NOT NULL,
				`next_payment` datetime NOT NULL,
				`reminder_at` datetime NOT NULL,
				`cancelled_at` datetime NOT NULL,
				`mollie_subscription_id` VARCHAR(64) NOT NULL,
				`mollie_customer_id` VARCHAR(64) NOT NULL,
				`payment_method` VARCHAR(64) NOT NULL,
				`id_mol_recurring_orders_product` INT(64) NOT NULL,
				`total_tax_incl` decimal(20, 6) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_update` datetime NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_recurring_orders_product` (
				`id_mol_recurring_orders_product`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_product` INT(64) NOT NULL,
				`id_product_attribute` INT(64) NOT NULL,
				`quantity` INT(64) NOT NULL,
				`unit_price` decimal(20,6) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_update` datetime NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return $sql;
    }

    private function alterTableCommands(): bool
    {
        $query = '
            SELECT COUNT(*) > 0 AS count
            FROM information_schema.columns
            WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . 'mol_recurring_order" AND column_name = "total_tax_incl";
        ';

        /* only run if it doesn't exist */
        if (Db::getInstance()->getValue($query)) {
            return true;
        }

        $query = '
            ALTER TABLE ' . _DB_PREFIX_ . 'mol_recurring_order
            ADD COLUMN total_tax_incl decimal(20, 6) NOT NULL;
        ';

        if (!Db::getInstance()->execute($query)) {
            return false;
        }

        $query = '
            UPDATE ' . _DB_PREFIX_ . 'mol_recurring_order ro
            JOIN ' . _DB_PREFIX_ . 'orders o ON ro.id_order = o.id_order
            SET ro.total_tax_incl = o.total_paid_tax_incl;
        ';

        if (!Db::getInstance()->execute($query)) {
            return false;
        }

        return true;
    }
}
