<?php

declare(strict_types=1);

namespace Mollie\Subscription\Install;

use Db;

final class DatabaseTableUninstaller extends AbstractUninstaller
{
    public function uninstall(): bool
    {
        foreach ($this->getCommands() as $query) {
            if (false == Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function getCommands(): array
    {
        $sql = [];

        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_recurring_order`;';

        return $sql;
    }
}
