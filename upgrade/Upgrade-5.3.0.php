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
use Mollie\Install\Installer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @return bool
 */
function upgrade_module_5_3_0(Mollie $module)
{
    Configuration::deleteByName('MOLLIE_PROFILE_ID');
    Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_CHARGEBACK, true);

    /** @var Installer $installer */
    $installer = $module->getMollieContainer(Installer::class);

    $installer->createChargedbackState();

    return true;
}
