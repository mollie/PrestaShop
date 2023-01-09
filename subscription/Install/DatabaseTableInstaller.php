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

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_sub_recurring_order` (
				`id_mol_sub_recurring_order`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_order` INT(64) NOT NULL,
				`description` VARCHAR(64) NOT NULL,
				`status` VARCHAR(64) NOT NULL,
				`quantity` INT(64) NOT NULL,
				`amount` decimal(20,6) NOT NULL,
				`currency_iso` VARCHAR(3) NOT NULL,
				`next_payment` datetime NOT NULL,
				`reminder_at` datetime NOT NULL,
				`cancelled_at` datetime NOT NULL,
				`mollie_sub_id` VARCHAR(64) NOT NULL,
				`mollie_customer_id` VARCHAR(64) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_update` datetime NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return $sql;
    }
}
