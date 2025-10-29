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
 * Upgrade to version 6.4.1
 * Ensures all tabs are properly registered (fixes issues from 6.4.0 upgrade)
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_1(Mollie $module): bool
{
    try {
        $installer = $module->getService(\Mollie\Install\Installer::class);

        // Define all tabs that should exist
        $tabsToInstall = [
            ['class_name' => 'AdminMollieModule_MTR', 'parent' => 'IMPROVE', 'name' => 'Mollie', 'visible' => true, 'icon' => 'mollie'],
            ['class_name' => 'AdminMollieModule', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Settings', 'visible' => false, 'icon' => ''],
            ['class_name' => 'AdminMollieSettings', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Settings', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAjax', 'parent' => 'AdminMollieModule', 'name' => 'AJAX', 'visible' => false, 'icon' => ''],
            ['class_name' => 'AdminMollieAuthenticationParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'API Configuration', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAuthentication', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'API Configuration', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMolliePaymentMethods', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Payment Methods', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAdvancedSettings', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Advanced Settings', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionOrders', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Subscriptions', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionFAQ', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Subscription FAQ', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieLogs', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Logs', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMolliePaymentMethodsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Payment Methods', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieAdvancedSettingsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Advanced Settings', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionOrdersParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Subscriptions', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieSubscriptionFAQParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Subscription FAQ', 'visible' => true, 'icon' => ''],
            ['class_name' => 'AdminMollieLogsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Logs', 'visible' => true, 'icon' => ''],
        ];

        $errors = [];

        // First, delete all old Mollie tabs in reverse order to handle parent-child relationships
        $classNames = array_column($tabsToInstall, 'class_name');
        $classNames[] = 'AdminMollieTabParent'; // Old tab that might still exist

        foreach (array_reverse($classNames) as $className) {
            $tabId = Tab::getIdFromClassName($className);
            if ($tabId) {
                $tab = new Tab($tabId);
                if (Validate::isLoadedObject($tab)) {
                    if (!$tab->delete()) {
                        $errors[] = "Failed to delete tab: {$className}";
                    }
                }
            }
        }

        // Now install all tabs fresh
        foreach ($tabsToInstall as $tabConfig) {
            $result = $installer->installTab(
                $tabConfig['class_name'],
                $tabConfig['parent'],
                $tabConfig['name'],
                $tabConfig['visible'],
                $tabConfig['icon']
            );

            if (!$result) {
                $errors[] = "Failed to install tab: {$tabConfig['class_name']}";
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

        // If we have errors, log them but don't fail the upgrade
        // This allows the module to continue working even if some tabs fail
        if (!empty($errors)) {
            PrestaShopLogger::addLog(
                sprintf('Mollie upgrade to 6.4.1 completed with warnings: %s', implode(', ', $errors)),
                2,
                null,
                'Module',
                $module->id,
                true
            );
        }

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
