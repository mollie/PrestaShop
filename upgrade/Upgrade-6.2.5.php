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

function upgrade_module_6_2_5(Mollie $module): bool
{
    // Create the new mol_payment_method_lang table
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_lang` (
                `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_method` TINYTEXT,
                `id_lang` INT(11),
                `id_shop` INT(11),
                `text` TINYTEXT,
                INDEX (`id_method`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $isTableCreated = Db::getInstance()->execute($sql);
    if (!$isTableCreated) {
        return false; // If the table creation fails, return false
    }

    return $isTableCreated;
}
