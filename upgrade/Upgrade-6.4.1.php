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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Install or update a tab using only core PrestaShop methods
 *
 * @param string $className
 * @param string|int $parent
 * @param string $name
 * @param bool $active
 * @param string $icon
 * @param string $moduleName
 *
 * @return bool
 */
function installMollieTab($className, $parent, $name, $active, $icon, $moduleName)
{
    $tabId = (int) Tab::getIdFromClassName($className);

    if ($tabId) {
        $tab = new Tab($tabId);
    } else {
        $tab = new Tab();
        $tab->class_name = $className;
    }

    $idParent = is_int($parent) ? $parent : (int) Tab::getIdFromClassName($parent);

    $tab->id_parent = $idParent;
    $tab->module = $moduleName;
    $tab->active = (bool) $active;

    if (!empty($icon)) {
        $tab->icon = $icon;
    }

    $languages = Language::getLanguages(true);
    foreach ($languages as $language) {
        $tab->name[$language['id_lang']] = $name;
    }

    return $tab->save();
}

/**
 * Upgrade to version 6.4.1
 * Ensures all tabs are properly registered using core PrestaShop methods only
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_1(Mollie $module): bool
{
    try {
        // Define all tabs that should exist
        $tabsToInstall = [
            ['class_name' => 'AdminMollieModule_MTR', 'parent' => 'IMPROVE', 'name' => 'Mollie', 'active' => true, 'icon' => 'mollie'],
            ['class_name' => 'AdminMollieModule', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Settings', 'active' => false, 'icon' => ''],
            ['class_name' => 'AdminMollieSettings', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Settings', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAjax', 'parent' => 'AdminMollieModule', 'name' => 'AJAX', 'active' => false, 'icon' => ''],
            ['class_name' => 'AdminMollieAuthenticationParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'API Configuration', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAuthentication', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'API Configuration', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMolliePaymentMethods', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Payment Methods', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAdvancedSettings', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Advanced Settings', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionOrders', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Subscriptions', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionFAQ', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Subscription FAQ', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieLogs', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Logs', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMolliePaymentMethodsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Payment Methods', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAdvancedSettingsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Advanced Settings', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionOrdersParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Subscriptions', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionFAQParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Subscription FAQ', 'active' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieLogsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Logs', 'active' => true, 'icon' => ''],
        ];

        // Remove old/obsolete tabs
        $obsoleteTabs = ['AdminMollieTabParent'];
        foreach ($obsoleteTabs as $className) {
            $tabId = (int) Tab::getIdFromClassName($className);
            if ($tabId) {
                $tab = new Tab($tabId);
                if (Validate::isLoadedObject($tab)) {
                    $tab->delete();
                }
            }
        }

        // Install/update all tabs
        $errors = [];
        foreach ($tabsToInstall as $tabConfig) {
            $result = installMollieTab(
                $tabConfig['class_name'],
                $tabConfig['parent'],
                $tabConfig['name'],
                $tabConfig['active'],
                $tabConfig['icon'],
                $module->name
            );

            if (!$result) {
                $errors[] = sprintf('Failed to install tab: %s', $tabConfig['class_name']);
                PrestaShopLogger::addLog(
                    sprintf('Mollie upgrade to 6.4.1: Failed to install tab %s', $tabConfig['class_name']),
                    3,
                    null,
                    'Module',
                    $module->id,
                    true
                );
            }
        }

        if (!empty($errors)) {
            PrestaShopLogger::addLog(
                sprintf('Mollie upgrade to 6.4.1 completed with errors: %s', implode(', ', $errors)),
                3,
                null,
                'Module',
                $module->id,
                true
            );
            return false;
        }

        PrestaShopLogger::addLog(
            'Mollie upgrade to 6.4.1: Tabs reinstalled successfully',
            1,
            null,
            'Module',
            $module->id,
            true
        );

        return true;
    } catch (Exception $e) {
        PrestaShopLogger::addLog(
            sprintf('Mollie upgrade to 6.4.1 failed: %s', $e->getMessage()),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }
}
