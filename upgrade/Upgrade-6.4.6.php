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

        // Has to run before any tab is installed below: those resolve their parent by class name
        // and the root tab is called AdminMollieModuleMTR from this version onwards.
        if (!mollieRenameRootTab('AdminMollieModule_MTR', 'AdminMollieModuleMTR')) {
            return false;
        }

        $installTabFunction($module, 'AdminMolliePaymentOverviewParent', 'AdminMollieModuleMTR', 'Payment overview');
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

/**
 * Renames the root menu tab from AdminMollieModule_MTR to AdminMollieModuleMTR.
 *
 * PrestaShop stores tab permissions as ROLE_MOD_TAB_<UPPERCASE CLASS NAME>_<ACTION> and reads them
 * back with the regex /ROLE_MOD_[A-Z]+_(?P<classname>[A-Z][A-Z0-9]*)_[A-Z]+/, which cannot hold an
 * underscore. ROLE_MOD_TAB_ADMINMOLLIEMODULE_MTR_READ was therefore parsed as class name
 * ADMINMOLLIEMODULE plus action MTR, so the permission landed on the hidden Settings tab and the
 * Mollie menu row always read back as denied.
 *
 * Tab::initAccess() only runs when a tab is created, so renaming the tab is not enough: the
 * existing role slugs have to be renamed too. Renaming them in place keeps their
 * id_authorization_role, which keeps the permissions merchants already granted.
 *
 * @param string $legacyClassName
 * @param string $newClassName
 *
 * @return bool
 */
function mollieRenameRootTab($legacyClassName, $newClassName)
{
    $legacyTabId = (int) Tab::getIdFromClassName($legacyClassName);

    if (!$legacyTabId) {
        return true;
    }

    $legacyTab = new Tab($legacyTabId);

    if (!Validate::isLoadedObject($legacyTab)) {
        return true;
    }

    $newTabId = (int) Tab::getIdFromClassName($newClassName);

    // A renamed tab already exists, so the legacy one is a leftover from an earlier install.
    // Tab::delete() drops its role slugs as well, but it never touches children, so they have
    // to be moved first or the whole Mollie menu is left pointing at a deleted row.
    if ($newTabId) {
        mollieMoveTabChildren($legacyTabId, $newTabId);

        $legacyTab->delete();
        Tab::resetStaticCache();

        return true;
    }

    $legacyTab->class_name = $newClassName;

    if (!$legacyTab->save()) {
        return false;
    }

    Tab::resetStaticCache();

    mollieRenameTabRoleSlugs($legacyClassName, $newClassName);

    return true;
}

/**
 * @param int $legacyTabId
 * @param int $newTabId
 *
 * @return void
 */
function mollieMoveTabChildren($legacyTabId, $newTabId)
{
    Db::getInstance()->update(
        'tab',
        ['id_parent' => (int) $newTabId],
        '`id_parent` = ' . (int) $legacyTabId
    );

    $newTab = new Tab((int) $newTabId);

    if (!Validate::isLoadedObject($newTab)) {
        return;
    }

    // The moved rows keep the positions they held under the legacy root, so they can collide.
    $newTab->cleanPositions((int) $newTabId);
}

/**
 * @param string $legacyClassName
 * @param string $newClassName
 *
 * @return void
 */
function mollieRenameTabRoleSlugs($legacyClassName, $newClassName)
{
    $table = _DB_PREFIX_ . 'authorization_role';

    foreach (['CREATE', 'READ', 'UPDATE', 'DELETE'] as $action) {
        $legacySlug = sprintf('ROLE_MOD_TAB_%s_%s', Tools::strtoupper($legacyClassName), $action);
        $newSlug = sprintf('ROLE_MOD_TAB_%s_%s', Tools::strtoupper($newClassName), $action);

        $legacyRoleId = (int) Db::getInstance()->getValue(
            'SELECT `id_authorization_role` FROM `' . $table . '` WHERE `slug` = "' . pSQL($legacySlug) . '"'
        );
        $newRoleId = (int) Db::getInstance()->getValue(
            'SELECT `id_authorization_role` FROM `' . $table . '` WHERE `slug` = "' . pSQL($newSlug) . '"'
        );

        // Nothing to rename, but the tab must still have a role for every action.
        if (!$legacyRoleId && !$newRoleId) {
            Db::getInstance()->execute(
                'INSERT INTO `' . $table . '` (`slug`) VALUES ("' . pSQL($newSlug) . '")'
            );

            continue;
        }

        if (!$legacyRoleId) {
            continue;
        }

        // The slug column is unique, so an already present target has to give way. Access granted
        // through the legacy slug was never visible in the back office, so it is safe to drop.
        if ($newRoleId) {
            Db::getInstance()->delete('access', '`id_authorization_role` = ' . $legacyRoleId);
            Db::getInstance()->delete('module_access', '`id_authorization_role` = ' . $legacyRoleId);
            Db::getInstance()->delete('authorization_role', '`id_authorization_role` = ' . $legacyRoleId);

            continue;
        }

        Db::getInstance()->update(
            'authorization_role',
            ['slug' => pSQL($newSlug)],
            '`id_authorization_role` = ' . $legacyRoleId
        );
    }
}
