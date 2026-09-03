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
        // Earlier installs and upgrades persisted tab names in the wrong language:
        // languages without a translations/<iso>.php file inherited the strings of
        // whichever language PrestaShop merged before them, and the install path
        // wrote the installing employee's language everywhere.
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
