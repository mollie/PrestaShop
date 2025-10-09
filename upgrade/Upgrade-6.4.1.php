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
 * - Ensure all admin tabs are installed: old subscription tabs and new React-based tabs
 */
function upgrade_module_6_4_1(Mollie $module): bool
{
    /** @var Installer $installer */
    $installer = $module->getService(Installer::class);

    // Install all tabs to ensure both old and new tabs are present
    // This handles upgrades from any previous version
    $installer->installSpecificTabs();

    PrestaShopLogger::addLog('Mollie upgrade 6.4.1: Successfully installed/updated all admin tabs', 1, null, 'Mollie', 1);
    return true;
}
