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
function upgrade_module_5_5_0(Mollie $module)
{
    $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD COLUMN min_amount decimal(20,6) DEFAULT 0,
        ADD COLUMN max_amount decimal(20,6) DEFAULT 0;
     ';

    return Db::getInstance()->execute($sql);
}
