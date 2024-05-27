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
use Mollie\Utility\PsVersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_2_0(Mollie $module): bool
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getService(ConfigurationAdapter::class);

    $configuration->updateValue(Config::MOLLIE_ERROR_LOGGING['sandbox'], 0);
    $configuration->updateValue(Config::MOLLIE_ERROR_LOGGING['production'], 0);

    return true;
}
