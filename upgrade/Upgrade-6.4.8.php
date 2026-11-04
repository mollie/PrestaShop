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
    // Fresh installs default the PrestaShop Account onboarding switch to off, while shops that already
    // run the module keep seeing the block. The global row is seeded so it becomes the fallback for
    // every shop without shadowing a shop-scoped value. A failure is logged and never fails the upgrade.
    try {
        if (false === Configuration::getGlobalValue(\Mollie\Config\Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED)) {
            Configuration::updateGlobalValue(\Mollie\Config\Config::MOLLIE_PS_ACCOUNTS_ONBOARDING_ENABLED, 1);
        }
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.8 could not seed the PrestaShop Account onboarding setting: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );
    }

    return true;
}
