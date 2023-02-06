<?php

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use Db;

final class DatabaseTableInstaller extends AbstractInstaller
{
    public function install(): bool
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
    private function getCommands(): array
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_recurring_order` (
				`id_mol_recurring_order`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_order` INT(64) NOT NULL,
				`id_cart` INT(64) NOT NULL,
				`id_currency` INT(64) NOT NULL,
				`id_customer` INT(64) NOT NULL,
				`description` VARCHAR(64) NOT NULL,
				`status` VARCHAR(64) NOT NULL,
				`next_payment` datetime NOT NULL,
				`reminder_at` datetime NOT NULL,
				`cancelled_at` datetime NOT NULL,
				`mollie_subscription_id` VARCHAR(64) NOT NULL,
				`mollie_customer_id` VARCHAR(64) NOT NULL,
				`payment_method` VARCHAR(64) NOT NULL,
				`id_mol_recurring_orders_product` INT(64) NOT NULL,
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
}
