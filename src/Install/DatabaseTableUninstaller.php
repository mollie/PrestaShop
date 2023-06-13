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
use Mollie\Factory\ModuleFactory;

final class DatabaseTableUninstaller implements UninstallerInterface
{
    /** @var ModuleFactory */
    private $moduleFactory;

    public function __construct(ModuleFactory $moduleFactory)
    {
        $this->moduleFactory = $moduleFactory;
    }

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
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_issuer`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_order_fee`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_carrier_information`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule`;';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_order_total_restriction`;';

        if ($moduleName = $this->moduleFactory->getModuleName()) {
            $sql[] = 'UPDATE ' . _DB_PREFIX_ . 'order_state SET deleted = 1 WHERE module_name = "' . pSQL($moduleName) . '";';
        }

        return $sql;
    }
}
