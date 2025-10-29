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

function upgrade_module_6_4_1(Mollie $module): bool
{
    try {
        // Get all tabs belonging to mollie module
        $sql = 'SELECT id_tab FROM ' . _DB_PREFIX_ . 'tab WHERE module = "mollie" ORDER BY id_tab DESC';
        $mollieTabIds = Db::getInstance()->executeS($sql);

        // Delete all mollie tabs (in reverse order - children first)
        if (is_array($mollieTabIds)) {
            foreach ($mollieTabIds as $tabData) {
                $tab = new Tab((int) $tabData['id_tab']);
                if (Validate::isLoadedObject($tab)) {
                    $tab->delete();
                }
            }
        }

        // Define all tabs to install
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

        // Install all tabs fresh
        $errors = [];
        foreach ($tabsToInstall as $tabConfig) {
            $tab = new Tab();
            $tab->class_name = $tabConfig['class_name'];

            // Get parent ID
            if ($tabConfig['parent'] === 'IMPROVE') {
                $tab->id_parent = (int) Tab::getIdFromClassName('IMPROVE');
            } else {
                $tab->id_parent = (int) Tab::getIdFromClassName($tabConfig['parent']);
            }

            $tab->module = $module->name;
            $tab->active = (bool) $tabConfig['active'];

            if (!empty($tabConfig['icon'])) {
                $tab->icon = $tabConfig['icon'];
            }

            // Set names for all languages
            $languages = Language::getLanguages(true);
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = $tabConfig['name'];
            }

            if (!$tab->save()) {
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
            'Mollie upgrade to 6.4.1: All tabs removed and reinstalled successfully',
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
