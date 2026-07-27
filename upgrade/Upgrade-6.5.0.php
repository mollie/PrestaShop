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
 * Add the api_key_ref column to mollie_payments so each order records a
 * reference of the API key that created it (multistore / key-migration safe).
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_5_0($module)
{
    try {
        $columnExists = (bool) Db::getInstance()->getValue(
            'SELECT COUNT(*) > 0
             FROM information_schema.columns
             WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '"
               AND table_name = "' . _DB_PREFIX_ . 'mollie_payments"
               AND column_name = "api_key_ref";'
        );

        if ($columnExists) {
            return true;
        }

        return (bool) Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments`
             ADD COLUMN `api_key_ref` VARCHAR(32) DEFAULT NULL AFTER `mandate_id`;'
        );
    } catch (Exception $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.5.0 failed: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }
}
