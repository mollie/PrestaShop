<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

use PrestaShop\PrestaShop\Adapter\Module\Tab\ModuleTabRegister;
use Symfony\Component\HttpFoundation\ParameterBag;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_2_3(Mollie $module): bool
{
    // Create the new mol_logs table
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_logs` (
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

    $isTableCreated = Db::getInstance()->execute($sql);
    if (!$isTableCreated) {
        return false; // If the table creation fails, return false
    }

    /** @var ModuleTabRegister $tabRegister */
    $tabRegister = $module->getService('prestashop.adapter.module.tab.register');

    $moduleAdapter = new \PrestaShop\PrestaShop\Adapter\Module\Module();
    $moduleAdapter->instance = $module;
    $moduleAdapter->disk = new ParameterBag(
        [
            'filemtype' => 0,
            'is_present' => 1,
            'is_valid' => 1,
            'version' => null,
            'path' => '',
        ]
    );

    $moduleAdapter->attributes->set('name', $module->name);

    $tabRegister->registerTabs($moduleAdapter);

    return $isTableCreated;
}
