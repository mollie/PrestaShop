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
 * Fixes missing AdminMollieSettings controller registration from 6.4.0
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_1(Mollie $module): bool
{
    try {
        $installer = $module->getService(\Mollie\Install\Installer::class);

        // Check if AdminMollieSettings tab already exists
        $tabId = Tab::getIdFromClassName('AdminMollieSettings');
        if ($tabId) {
            // Tab already exists, no need to install
            return true;
        }

        // Install the missing AdminMollieSettings tab
        $result = $installer->installTab(
            'AdminMollieSettings',
            'AdminMollieModule_MTR',
            'Settings',
            true
        );

        if (!$result) {
            PrestaShopLogger::addLog(
                'Mollie upgrade to 6.4.1 failed: Could not install AdminMollieSettings tab',
                3,
                null,
                'Module',
                $module->id,
                true
            );
            return false;
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
