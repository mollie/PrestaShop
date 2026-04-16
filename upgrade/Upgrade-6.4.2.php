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
function upgrade_module_6_4_2($module)
{
    try {
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'mol_payment_method` SET `enabled` = 0 WHERE `id_method` = "googlepay"'
        );

        $tabNames = [
            'AdminMollieAuthenticationParent' => 'API Configuration',
            'AdminMollieAuthentication' => 'API Configuration',
            'AdminMolliePaymentMethodsParent' => 'Payment Methods',
            'AdminMolliePaymentMethods' => 'Payment Methods',
            'AdminMollieAdvancedSettingsParent' => 'Advanced Settings',
            'AdminMollieAdvancedSettings' => 'Advanced Settings',
            'AdminMollieSubscriptionOrdersParent' => 'Subscriptions',
            'AdminMollieSubscriptionOrders' => 'Subscriptions',
            'AdminMollieSubscriptionFAQParent' => 'Subscription FAQ',
            'AdminMollieSubscriptionFAQ' => 'Subscription FAQ',
            'AdminMollieLogsParent' => 'Logs',
            'AdminMollieLogs' => 'Logs',
        ];

        $languages = Language::getLanguages(false);

        foreach ($tabNames as $className => $englishName) {
            $tabId = (int) Tab::getIdFromClassName($className);

            if (!$tabId) {
                continue;
            }

            $tab = new Tab($tabId);

            foreach ($languages as $language) {
                $translatedName = Translate::getModuleTranslation(
                    $module,
                    $englishName,
                    $module->name,
                    null,
                    false,
                    $language['locale']
                );

                $tab->name[$language['id_lang']] = $translatedName ?: $englishName;
            }

            $tab->save();
        }

        return true;
    } catch (Exception $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.2 failed: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }
}
