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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_4_2(Mollie $module): bool
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getMollieContainer(ConfigurationAdapter::class);

    $configuration->updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT, (int) $configuration->get('MOLLIE_APPLE_PAY_DIRECT'));
    $configuration->updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_CART, (int) $configuration->get('MOLLIE_APPLE_PAY_DIRECT'));

    $configuration->delete('MOLLIE_APPLE_PAY_DIRECT');

    return true;
}
