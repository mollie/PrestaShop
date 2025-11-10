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

function upgrade_module_6_4_0(Mollie $module): bool
{
    try {
        // Helper function to install a tab without dependency injection
        // This bypasses the service container to avoid dependency issues during upgrade
        $installTabFunction = function($module, $className, $parent, $name, $active = true, $icon = '') {
            $tabId = Tab::getIdFromClassName($className);
            if ($tabId) {
                $moduleTab = new Tab($tabId);
            } else {
                $moduleTab = new Tab();
                $moduleTab->class_name = $className;
            }

            $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

            if (!$idParent && $parent !== -1 && $parent !== 0) {
                return false;
            }

            $moduleTab->id_parent = $idParent;
            $moduleTab->module = $module->name;
            $moduleTab->active = $active;
            if (!empty($icon)) {
                $moduleTab->icon = $icon;
            }

            $languages = Language::getLanguages(true);
            foreach ($languages as $language) {
                $moduleTab->name[$language['id_lang']] = $module->l($name, false, $language['iso_code']);
            }

            return $moduleTab->save();
        };

        // Prepare tabs configuration
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

        // Delete old tabs
        foreach (array_reverse($classNames) as $className) {
            $tabId = Tab::getIdFromClassName($className);
            if ($tabId) {
                $tab = new Tab($tabId);
                if (Validate::isLoadedObject($tab)) {
                    $tab->delete();
                }
            }
        }

        // Install new tabs
        foreach ($tabsToInstall as $tabConfig) {
            $installTabFunction(
                $module,
                $tabConfig['class_name'],
                $tabConfig['parent'],
                $tabConfig['name'],
                $tabConfig['visible'],
                $tabConfig['icon'] ?? ''
            );
        }

        return true;
    } catch (Exception $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.0 failed: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }
}
