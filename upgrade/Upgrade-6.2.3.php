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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;

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

    // Install the necessary tabs
    $tabs = [
        [
            'name' => $module->l('Logs'),
            'class_name' => $module::ADMIN_MOLLIE_LOGS_PARENT_CONTROLLER,
            'parent_class_name' => $module::ADMIN_MOLLIE_CONTROLLER,
            'module_tab' => true,
        ],
        [
            'name' => $module->l('Logs'),
            'class_name' => $module::ADMIN_MOLLIE_LOGS_CONTROLLER,
            'parent_class_name' => $module::ADMIN_MOLLIE_TAB_CONTROLLER,
            'module_tab' => true,
        ],
    ];

    foreach ($tabs as $tabData) {
        $tab = new Tab();
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabData['name'];
        }
        $tab->class_name = $tabData['class_name'];
        $tab->id_parent = Tab::getIdFromClassName($tabData['parent_class_name']);
        $tab->module = $module->name;
        $tab->active = 1;

        if (!$tab->add()) {
            return false; // Return false if any tab creation fails
        }
    }

    return $isTableCreated;
}
