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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_3_3()
{
    $query = 'DROP TABLE ' . _DB_PREFIX_ . 'mol_payment_method_order_total_restriction';

    if (!Db::getInstance()->execute($query)) {
        return false;
    }

    return true;
}
