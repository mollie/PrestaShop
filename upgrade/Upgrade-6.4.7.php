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
function upgrade_module_6_4_7($module)
{
    // Shops that installed before displayBackOfficeHeader joined the install hook list never got it,
    // so the manual order screen lacks the payment email checkbox. Active shops only: for any other
    // shop Hook::registerHook also deletes the module's module_shop row, disabling Mollie there.
    // A failure is logged and never fails the upgrade, the hook can still be set in Design > Positions.
    try {
        if (!$module->registerHook('displayBackOfficeHeader', Shop::getShops(true, null, true))) {
            PrestaShopLogger::addLog(
                'Mollie module upgrade to 6.4.7 could not register the displayBackOfficeHeader hook',
                3,
                null,
                'Module',
                $module->id,
                true
            );
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.7 could not register the displayBackOfficeHeader hook: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    return true;
}
