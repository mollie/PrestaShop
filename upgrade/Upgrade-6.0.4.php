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

use Mollie\Utility\PsVersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_4(Mollie $module): bool
{
    if (PsVersionUtility::isPsVersionGreaterOrEqualTo(_PS_VERSION_, '1.7.7.0')) {
        $module->unregisterHook('actionFrontControllerAfterInit');
        $module->registerHook('actionFrontControllerInitAfter');
    }

    return true;
}
