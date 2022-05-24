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

use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_5_1_0_b($module)
{
    $module->registerHook('displayExpressCheckout');
    $module->registerHook('displayProductActions');
    $module->registerHook('actionObjectOrderPaymentAddAfter');
    Configuration::updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE, 0);

    return true;
}
