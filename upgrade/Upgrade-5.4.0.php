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
function upgrade_module_5_4_0(Mollie $module)
{
    $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mollie_payments
        ADD `mandate_id` VARCHAR(64);
    ';

    if (!Db::getInstance()->execute($sql)) {
        return false;
    }
    return true;
}
