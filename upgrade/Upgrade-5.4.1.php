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

function upgrade_module_5_4_1(Mollie $module): bool
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getMollieContainer(ConfigurationAdapter::class);

    $configuration->updateValue(Config::MOLLIE_IFRAME['production'], Configuration::get('MOLLIE_IFRAME'));
    $configuration->updateValue(Config::MOLLIE_IFRAME['sandbox'], Configuration::get('MOLLIE_IFRAME'));

    $configuration->updateValue(Config::MOLLIE_ISSUERS['production'], Configuration::get('MOLLIE_ISSUERS'));
    $configuration->updateValue(Config::MOLLIE_ISSUERS['sandbox'], Configuration::get('MOLLIE_ISSUERS'));

    $configuration->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT['production'], Configuration::get('MOLLIE_SINGLE_CLICK_PAYMENT'));
    $configuration->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT['sandbox'], Configuration::get('MOLLIE_SINGLE_CLICK_PAYMENT'));

    return true;
}
