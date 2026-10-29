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
function upgrade_module_6_4_8($module)
{
    // install() is not replayed on an upgrade: shops that already run the module need the hook that
    // keeps a subscription cart on Mollie payment methods. Active shops only, as in 6.4.7: for any
    // other shop Hook::registerHook also deletes the module's module_shop row. A failure is logged and
    // never fails the upgrade, the hook can still be set in Design > Positions.
    try {
        if (!$module->registerHook('actionPresentPaymentOptions', Shop::getShops(true, null, true))) {
            PrestaShopLogger::addLog(
                'Mollie module upgrade to 6.4.8 could not register the actionPresentPaymentOptions hook',
                3,
                null,
                'Module',
                $module->id,
                true
            );
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not register the actionPresentPaymentOptions hook: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    return true;
}
