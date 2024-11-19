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

final class DatabaseTableUninstaller implements UninstallerInterface
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

        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_country`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_carrier_information`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_order_total_restriction`;';

        return $sql;
    }
}
