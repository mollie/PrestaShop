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

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_0_1(Mollie $module): bool
{
    /** @var ConfigurationAdapter $configuration */
    $configuration = $module->getService(ConfigurationAdapter::class);

    $configuration->updateValue(Config::MOLLIE_IFRAME['production'], Configuration::get('MOLLIE_IFRAME'));
    $configuration->updateValue(Config::MOLLIE_IFRAME['sandbox'], Configuration::get('MOLLIE_IFRAME'));

    $configuration->updateValue(Config::MOLLIE_ISSUERS['production'], Configuration::get('MOLLIE_ISSUERS'));
    $configuration->updateValue(Config::MOLLIE_ISSUERS['sandbox'], Configuration::get('MOLLIE_ISSUERS'));

    $configuration->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT['production'], Configuration::get('MOLLIE_SINGLE_CLICK_PAYMENT'));
    $configuration->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT['sandbox'], Configuration::get('MOLLIE_SINGLE_CLICK_PAYMENT'));

    if (!modifyExistingTables()) {
        return false;
    }

    return true;
}

function modifyExistingTables(): bool
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
        CHANGE surcharge_fixed_amount surcharge_fixed_amount_tax_excl decimal(20,2),
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

    $sql = '
    SELECT COUNT(*) > 0 AS count
    FROM information_schema.columns
    WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . 'mol_order_payment_fee";
    ';

    /** only add it if it doesn't exist */
    if (!(int) Db::getInstance()->getValue($sql)) {
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'mol_order_fee MODIFY id_mol_order_fee INT(64)';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'mol_order_fee DROP PRIMARY KEY';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        $sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_order_fee
        CHANGE order_fee fee_tax_incl decimal(20,6)  NOT NULL,
        CHANGE id_mol_order_fee id_mol_order_payment_fee INT AUTO_INCREMENT PRIMARY KEY,
        ADD COLUMN id_order INT(64) NOT NULL,
        ADD COLUMN fee_tax_excl decimal(20,6) NOT NULL;
        ';

        try {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        $sql = 'RENAME TABLE ' . _DB_PREFIX_ . 'mol_order_fee TO ' . _DB_PREFIX_ . 'mol_order_payment_fee';

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
