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
    Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
        VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Starting upgrade', NOW())");

    try {
        // Helper function to install a tab without dependency injection
        $installTabFunction = function($module, $className, $parent, $name, $active = true, $icon = '') {
            // Check if tab already exists
            $tabId = Tab::getIdFromClassName($className);
            if ($tabId) {
                $moduleTab = new Tab($tabId);
                Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                    VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Tab already exists, updating className=" . pSQL($className) . "', NOW())");
            } else {
                $moduleTab = new Tab();
                $moduleTab->class_name = $className;
                Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                    VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Creating new tab className=" . pSQL($className) . "', NOW())");
            }

            $idParent = is_int($parent) ? $parent : Tab::getIdFromClassName($parent);

            if (!$idParent && $parent !== -1 && $parent !== 0) {
                Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                    VALUES (3, 0, 'ERROR upgrade_module_6_4_0: Parent tab not found for className=" . pSQL($className) . ", parent=" . pSQL($parent) . "', NOW())");
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

            if (!$moduleTab->save()) {
                Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                    VALUES (3, 0, 'ERROR upgrade_module_6_4_0: Failed to save tab className=" . pSQL($className) . "', NOW())");
                return false;
            }

            return true;
        };

        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Helper function created, bypassing service container', NOW())");

        // Step 2: Prepare tabs configuration
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

        // Step 3: Delete old tabs
        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Starting tab deletion, total count=" . count($classNames) . "', NOW())");

        $deletedCount = 0;
        foreach (array_reverse($classNames) as $className) {
            $tabId = Tab::getIdFromClassName($className);
            if ($tabId) {
                Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                    VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Found existing tab className=" . pSQL($className) . ", tabId=" . (int)$tabId . "', NOW())");

                $tab = new Tab($tabId);
                if (Validate::isLoadedObject($tab)) {
                    if ($tab->delete()) {
                        $deletedCount++;
                        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Successfully deleted tab className=" . pSQL($className) . "', NOW())");
                    } else {
                        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                            VALUES (3, 0, 'WARNING upgrade_module_6_4_0: Failed to delete tab className=" . pSQL($className) . "', NOW())");
                    }
                }
            }
        }

        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Tab deletion completed, deleted " . (int)$deletedCount . " tabs', NOW())");

        // Step 4: Install new tabs
        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Starting tab installation, total count=" . count($tabsToInstall) . "', NOW())");

        $installedCount = 0;
        foreach ($tabsToInstall as $tabConfig) {
            Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Installing tab className=" . pSQL($tabConfig['class_name']) . ", parent=" . pSQL($tabConfig['parent']) . "', NOW())");

            try {
                $result = $installTabFunction(
                    $module,
                    $tabConfig['class_name'],
                    $tabConfig['parent'],
                    $tabConfig['name'],
                    $tabConfig['visible'],
                    $tabConfig['icon'] ?? ''
                );

                if ($result) {
                    $installedCount++;
                    Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                        VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Successfully installed tab className=" . pSQL($tabConfig['class_name']) . "', NOW())");
                } else {
                    Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                        VALUES (3, 0, 'ERROR upgrade_module_6_4_0: installTab returned false for className=" . pSQL($tabConfig['class_name']) . "', NOW())");
                }
            } catch (Exception $tabException) {
                Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
                    VALUES (3, 0, 'ERROR upgrade_module_6_4_0: Exception installing tab className=" . pSQL($tabConfig['class_name']) . " - " . pSQL($tabException->getMessage()) . "', NOW())");
            }
        }

        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: Tab installation completed, installed " . (int)$installedCount . " tabs', NOW())");

        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (1, 0, 'DEBUG upgrade_module_6_4_0: All operations completed successfully', NOW())");

        return true;

    } catch (Exception $e) {
        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (3, 0, 'ERROR upgrade_module_6_4_0: Exception caught - " . pSQL($e->getMessage()) . " at " . pSQL($e->getFile()) . ":" . (int)$e->getLine() . "', NOW())");

        Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "log` (severity, error_code, message, date_add)
            VALUES (3, 0, 'ERROR upgrade_module_6_4_0: Stack trace - " . pSQL($e->getTraceAsString()) . "', NOW())");

        return false;
    }
}
