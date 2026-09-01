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
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_6($module)
{
    try {
        $legacyClassName = 'AdminMollieModule_MTR';
        $newClassName = 'AdminMollieModuleMTR';

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
