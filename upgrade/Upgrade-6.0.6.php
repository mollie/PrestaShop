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

function upgrade_module_6_0_6(Mollie $module): bool
{
    updateConfigurationValues606($module);

    return true;
}

function updateConfigurationValues606(Mollie $module)
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getService(ConfigurationAdapter::class);

    $configuration->updateValue(Config::MOLLIE_SUBSCRIPTION_ENABLED, '0');
}
