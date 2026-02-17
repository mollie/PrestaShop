<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_2($module)
{
    $result = Db::getInstance()->execute(
        'UPDATE `' . _DB_PREFIX_ . 'mol_payment_method` SET `enabled` = 0 WHERE `id_method` = "googlepay"'
    );

    return (bool) $result;
}
