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
 * Add is_seen column to mollie_payments table for webhook improvements
 *
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_6_4_2(Mollie $module): bool
{
    try {
        $db = Db::getInstance();

        // Check if column already exists to prevent errors on re-upgrade
        $columnExists = $db->getValue(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = \'' . _DB_PREFIX_ . 'mollie_payments\' 
            AND COLUMN_NAME = \'is_seen\''
        );

        if (!$columnExists) {
            $sql = '
                ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments`
                ADD COLUMN `is_seen` BOOLEAN DEFAULT 0
                AFTER `bank_status`
            ';

            $result = $db->execute($sql);

            if (!$result) {
                PrestaShopLogger::addLog(
                    'Mollie module upgrade to 6.4.2 failed: Could not add is_seen column',
                    3,
                    null,
                    'Module',
                    $module->id,
                    true
                );

                return false;
            }
        }

        return true;
    } catch (Throwable $e) {
        PrestaShopLogger::addLog(
            'Mollie module upgrade to 6.4.2 failed: ' . $e->getMessage(),
            3,
            $e->getCode(),
            'Module',
            $module->id,
            true
        );

        return false;
    }
}
