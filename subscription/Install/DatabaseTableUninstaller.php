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
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_recurring_orders_product`;';

        return $sql;
    }
}
