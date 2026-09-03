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

namespace Mollie\Utility;

use Language;
use Module;
use Tab;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Resolves tab names per language without Translate::getModuleTranslation().
 * That core helper merges every language file into one flat global keyed without
 * a language dimension, so a language the module ships no translation file for
 * silently returns the strings of whichever language was merged before it.
 * Reading each file in isolation keeps languages apart and falls back to the
 * English source string when the file or the key does not exist.
 */
class TabTranslationUtility
{
    /** Every Mollie tab, so a repair can rewrite names persisted in the wrong language. */
    const TAB_NAMES = [
        'AdminMollieModule_MTR' => 'Mollie',
        'AdminMollieModule' => 'Settings',
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

    /** @var array<string, array<string, string>> */
    private static $fileCache = [];

    /**
     * Rewrites every existing Mollie tab name from the per-language translation
     * files, repairing rows an earlier install or upgrade persisted in the wrong
     * language. Tabs that do not exist on this shop are skipped.
     *
     * @param Module $module
     *
     * @return void
     */
    public static function repairTabNames(Module $module)
    {
        $languages = Language::getLanguages(false);

        foreach (self::TAB_NAMES as $className => $englishName) {
            $tabId = (int) Tab::getIdFromClassName($className);

            if (!$tabId) {
                continue;
            }

            $tab = new Tab($tabId);

            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = self::getTabName($module, $englishName, $language['iso_code']);
            }

            $tab->save();
        }
    }

    /**
     * @param Module $module
     * @param string $englishName
     *
     * @return array tab name per language, keyed by iso code as Module::getTabs() expects
     */
    public static function getTabNames(Module $module, $englishName)
    {
        $names = [];

        foreach (Language::getLanguages(false) as $language) {
            $names[$language['iso_code']] = self::getTabName($module, $englishName, $language['iso_code']);
        }

        return $names;
    }

    /**
     * @param Module $module
     * @param string $englishName
     * @param string $isoCode
     *
     * @return string
     */
    public static function getTabName(Module $module, $englishName, $isoCode)
    {
        return self::translate(
            _PS_MODULE_DIR_ . $module->name . '/translations',
            $module->name,
            $englishName,
            $isoCode
        );
    }

    /**
     * @param string $translationsDir
     * @param string $moduleName
     * @param string $englishName
     * @param string $isoCode
     *
     * @return string
     */
    public static function translate($translationsDir, $moduleName, $englishName, $isoCode)
    {
        $translations = self::loadTranslationFile($translationsDir . '/' . strtolower((string) $isoCode) . '.php');

        $escapedName = (string) preg_replace("/\\\\*'/", "\\'", $englishName);
        $key = strtolower('<{' . $moduleName . '}prestashop>' . $moduleName) . '_' . md5($escapedName);

        if (!empty($translations[$key]) && is_string($translations[$key])) {
            return stripslashes($translations[$key]);
        }

        return $englishName;
    }

    /**
     * @param string $filePath
     *
     * @return array
     */
    private static function loadTranslationFile($filePath)
    {
        if (isset(self::$fileCache[$filePath])) {
            return self::$fileCache[$filePath];
        }

        if (!file_exists($filePath)) {
            return self::$fileCache[$filePath] = [];
        }

        // Translation files declare `global $_MODULE`, so including one from any
        // scope overwrites the global PrestaShop merges translations into.
        global $_MODULE;
        $globalBackup = isset($_MODULE) ? $_MODULE : null;
        $_MODULE = [];

        include $filePath;

        $translations = (array) $_MODULE;
        $_MODULE = $globalBackup;

        return self::$fileCache[$filePath] = $translations;
    }
}
