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

function upgrade_module_6_0_1(Mollie $module): bool
{
    $sql = '
    SELECT COUNT(*) > 0 AS count
    FROM information_schema.columns
    WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . 'mol_payment_method" AND column_name = "tax_rules_group_id";
    ';

    /** only add it if it doesn't exist */
    if (!(int) Db::getInstance()->getValue($sql)) {
        $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        CHANGE surcharge_fixed_amount surcharge_fixed_amount_tax_excl decimal(20,6),
        ADD COLUMN tax_rules_group_id int(10) DEFAULT 0;
        ';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    return true;
}
