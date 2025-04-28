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
function upgrade_module_6_2_9(Mollie $module)
{
    $sql = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_customer_group` (
            `id_payment_method` int(11) NOT NULL,
            `id_customer_group` int(11) NOT NULL,
            PRIMARY KEY (`id_payment_method`, `id_customer_group`),
            KEY `id_customer_group` (`id_customer_group`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ';

    return Db::getInstance()->execute($sql);
} 