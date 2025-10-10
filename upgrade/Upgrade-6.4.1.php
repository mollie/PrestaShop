<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_4_1(Mollie $module): bool
{
    try {
        $installer = $module->getService(\Mollie\Install\Installer::class);

        $tabsToInstall = [
            ['class_name' => 'AdminMollieModule_MTR', 'parent' => 'IMPROVE', 'name' => 'Mollie', 'visible' => true, 'icon' => 'mollie'],
            ['class_name' => 'AdminMollieModule', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Settings', 'visible' => false],
            ['class_name' => 'AdminMollieAjax', 'parent' => 'AdminMollieModule', 'name' => 'AJAX', 'visible' => false],
            ['class_name' => 'AdminMollieAuthenticationParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'API Configuration', 'visible' => true],
            ['class_name' => 'AdminMollieAuthentication', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'API Configuration', 'visible' => true],
            ['class_name' => 'AdminMolliePaymentMethods', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Payment Methods', 'visible' => true],
            ['class_name' => 'AdminMollieAdvancedSettings', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Advanced Settings', 'visible' => true],
            ['class_name' => 'AdminMollieSubscriptionOrders', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Subscriptions', 'visible' => true],
            ['class_name' => 'AdminMollieSubscriptionFAQ', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Subscription FAQ', 'visible' => true],
            ['class_name' => 'AdminMollieLogs', 'parent' => 'AdminMollieAuthenticationParent', 'name' => 'Logs', 'visible' => true],
            ['class_name' => 'AdminMolliePaymentMethodsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Payment Methods', 'visible' => true],
            ['class_name' => 'AdminMollieAdvancedSettingsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Advanced Settings', 'visible' => true],
            ['class_name' => 'AdminMollieSubscriptionOrdersParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Subscriptions', 'visible' => true],
            ['class_name' => 'AdminMollieSubscriptionFAQParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Subscription FAQ', 'visible' => true],
            ['class_name' => 'AdminMollieLogsParent', 'parent' => 'AdminMollieModule_MTR', 'name' => 'Logs', 'visible' => true],
        ];

        $classNames = array_column($tabsToInstall, 'class_name');
        $classNames[] = 'AdminMollieTabParent';

        foreach (array_reverse($classNames) as $className) {
            $tabId = Tab::getIdFromClassName($className);
            if ($tabId) {
                $tab = new Tab($tabId);
                if (Validate::isLoadedObject($tab)) {
                    $tab->delete();
                }
            }
        }

        foreach ($tabsToInstall as $tabConfig) {
            $installer->installTab(
                $tabConfig['class_name'],
                $tabConfig['parent'],
                $tabConfig['name'],
                $tabConfig['visible'],
                $tabConfig['icon'] ?? ''
            );
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}
