<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

use Mollie\Install\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade to version 6.4.1
 * - Add new admin tabs: Authentication, Payment Methods, Advanced Settings
 */
function upgrade_module_6_4_1(Mollie $module): bool
{
    /** @var Installer $installer */
    $installer = $module->getService(Installer::class);

    // Install new admin tabs for modern React-based interface
    // These tabs appear within the Settings configuration page as sub-tabs
    $tabsInstalled = true;
    $tabsInstalled &= $installer->installTab('AdminMollieAuthentication', 'AdminMollieModule', 'API Configuration', true, '');
    $tabsInstalled &= $installer->installTab('AdminMolliePaymentMethods', 'AdminMollieModule', 'Payment Methods', true, '');
    $tabsInstalled &= $installer->installTab('AdminMollieAdvancedSettings', 'AdminMollieModule', 'Advanced Settings', true, '');

    if (!$tabsInstalled) {
        PrestaShopLogger::addLog('Mollie upgrade 6.4.1: Failed to install new admin tabs', 3, null, 'Mollie', 1);
        return false;
    }

    PrestaShopLogger::addLog('Mollie upgrade 6.4.1: Successfully installed new admin tabs', 1, null, 'Mollie', 1);
    return true;
}
