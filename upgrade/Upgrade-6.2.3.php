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
use Mollie\Install\InstallerInterface;
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

    if (!deleteOldTabs623($module)) {
        return false;
    }

    // only for 1.7.6 version
    if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
        if (!deleteOldTabAuthorizationRoles623($module)) {
            return false;
        }
    }

    /** @var Mollie\Install\Installer $installer */
    $installer = $module->getService(Mollie\Install\Installer::class);

    $installer->installSpecificTabs();

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

function deleteOldTabs623($module): bool
{
    $classNames = [$module::ADMIN_MOLLIE_CONTROLLER, $module::ADMIN_MOLLIE_AJAX_CONTROLLER];
    $preparedClassNames = [];

    foreach ($classNames as $className) {
        $preparedClassNames[] = pSQL($className);
    }

    $preparedClassNames = implode("', '", $preparedClassNames);

    $sql = '
        SELECT id_tab
        FROM `' . _DB_PREFIX_ . 'tab`
        WHERE class_name IN (\'' . $preparedClassNames . '\');
    ';

    try {
        $tabIds = Db::getInstance()->executeS($sql);
    } catch (Exception $e) {
        return false;
    }

    if (empty($tabIds)) {
        return true;
    }

    $preparedTabIds = [];

    foreach ($tabIds as $tabId) {
        $preparedTabIds[] = (int) $tabId['id_tab'];
    }

    $preparedTabIds = implode(", ", $preparedTabIds);

    $sql = '
    DELETE FROM `' . _DB_PREFIX_ . 'tab`
    WHERE id_tab IN (' . $preparedTabIds . ');
    ';

    try {
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    $sql = '
    DELETE FROM `' . _DB_PREFIX_ . 'tab_lang`
    WHERE id_tab IN (' . $preparedTabIds . ');
    ';

    try {
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    return true;
}

function deleteOldTabAuthorizationRoles623($module): bool
{
    $controllers = [$module::ADMIN_MOLLIE_CONTROLLER, $module::ADMIN_MOLLIE_AJAX_CONTROLLER];
    $preparedTabs = [];

    foreach ($controllers as $controller) {
        $preparedTabs[] = 'ROLE_MOD_TAB_' . pSQL($controller) . '_CREATE';
        $preparedTabs[] = 'ROLE_MOD_TAB_' . pSQL($controller) . '_READ';
        $preparedTabs[] = 'ROLE_MOD_TAB_' . pSQL($controller) . '_UPDATE';
        $preparedTabs[] = 'ROLE_MOD_TAB_' . pSQL($controller) . '_DELETE';
    }

    $preparedTabs = implode("', '", $preparedTabs);

    $sql = '
        DELETE FROM `' . _DB_PREFIX_ . 'authorization_role`
        WHERE slug IN (\'' . $preparedTabs . '\');
        ';

    try {
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

    return true;
}