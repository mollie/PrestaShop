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

function upgrade_module_6_2_7(Mollie $module): bool
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'mol_payment_method_lang` RENAME TO ' . _DB_PREFIX_ . 'mol_payment_method_translations;';

    return Db::getInstance()->execute($sql);
}
