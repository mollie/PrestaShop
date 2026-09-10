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
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_6($module)
{
    try {
        // Bypasses the service container, which is not reliably available during an upgrade.
        $installTabFunction = function ($module, $className, $parent, $name) {
            $tabId = (int) Tab::getIdFromClassName($className);
            $moduleTab = $tabId ? new Tab($tabId) : new Tab();

            if (!$tabId) {
                $moduleTab->class_name = $className;
            }

            $idParent = (int) Tab::getIdFromClassName($parent);

            if (!$idParent) {
                return false;
            }

            $moduleTab->id_parent = $idParent;
            $moduleTab->module = $module->name;
            $moduleTab->active = true;

            foreach (Language::getLanguages(false) as $language) {
                // Translate::getModuleTranslation() merges every language file into one flat
                // global keyed without a language dimension, so a language the module ships no
                // translations/<iso>.php for returns the strings of whichever language was
                // merged before it. The utility reads each file in isolation instead.
                $moduleTab->name[$language['id_lang']] = \Mollie\Utility\TabTranslationUtility::getTabName(
                    $module,
                    $name,
                    $language['iso_code']
                );
            }

            return (bool) $moduleTab->save();
        };

        // The payment overview needs both index shapes and uses one or the other depending on
        // the filter. Without them the list full scans a table that survives uninstall and is
        // never pruned. created_at alone lets the default view walk the index backwards and stop
        // at the page size, while bank_status first turns a status filter into a range instead of
        // a full index walk with a row lookup per entry. ADD INDEX is an online DDL on MySQL 5.6
        // and MariaDB 10.0 upwards, so it does not block writes on a large shop.
        $addIndexesFunction = function () {
            $indexes = [
                'mollie_payments_status_created' => '`bank_status`, `created_at`',
                'mollie_payments_created_at' => '`created_at`',
            ];

            foreach ($indexes as $name => $columns) {
                $exists = Db::getInstance()->getValue('
                    SELECT COUNT(*) > 0
                    FROM information_schema.statistics
                    WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '"
                        AND TABLE_NAME = "' . _DB_PREFIX_ . 'mollie_payments"
                        AND INDEX_NAME = "' . pSQL($name) . '";
                ');

                if ($exists) {
                    continue;
                }

                Db::getInstance()->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments`
                    ADD INDEX `' . bqSQL($name) . '` (' . $columns . ');
                ');
            }
        };

        $installTabFunction($module, 'AdminMolliePaymentOverviewParent', 'AdminMollieModule_MTR', 'Payment overview');
        $installTabFunction($module, 'AdminMolliePaymentOverview', 'AdminMollieAuthenticationParent', 'Payment overview');

        $addIndexesFunction();

        // Earlier installs and upgrades persisted tab names in the wrong language: languages
        // without a translations/<iso>.php file inherited the strings of whichever language
        // PrestaShop merged before them, and the install path wrote the installing employee's
        // language everywhere. Those rows are only rewritten when a tab is created or updated,
        // so shops that already upgraded need an explicit repair.
        \Mollie\Utility\TabTranslationUtility::repairTabNames($module);

        return true;
    } catch (Exception $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.6 failed: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }
}
