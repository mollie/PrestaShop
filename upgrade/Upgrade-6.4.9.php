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
function upgrade_module_6_4_9($module)
{
    // The subscription FAQ now lives on the Subscriptions tab, so its own tabs are removed.
    try {
        foreach (['AdminMollieSubscriptionFAQ', 'AdminMollieSubscriptionFAQParent'] as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);

            if (!$idTab) {
                continue;
            }

            (new Tab($idTab))->delete();
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.9 could not remove the Subscription FAQ tabs: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    return true;
}
