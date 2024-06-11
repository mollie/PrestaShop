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

function upgrade_module_6_2_1(Mollie $module): bool
{
    $isTableDeleted = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_issuer`');
    $isSandboxConfigDeleted = Configuration::deleteByName('MOLLIE_SANDBOX_ISSUERS');
    $isProdConfigDeleted = Configuration::deleteByName('MOLLIE_PRODUCTION_ISSUERS');

    return $isTableDeleted && $isSandboxConfigDeleted && $isProdConfigDeleted;
}