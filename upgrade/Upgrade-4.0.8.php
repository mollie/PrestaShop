<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 */

use Mollie\Config\Config;
use Mollie\Utility\MultiLangUtility;

if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @return bool
 *
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgrade_module_4_0_8()
{
	Configuration::updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT, 0);
	Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, Config::ENVIRONMENT_LIVE);

	$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_customer` (
				`id_mol_customer`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`customer_id` VARCHAR(64) NOT NULL,
				`name` VARCHAR(64) NOT NULL,
				`email` VARCHAR(64) NOT NULL,
				`created_at` VARCHAR(64) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';
	$sql .= '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD COLUMN live_environment TINYINT(1) DEFAULT 1;
     ';

	$sql .= '
        ALTER TABLE `' . _DB_PREFIX_ . 'mollie_payments` ADD INDEX(`order_reference`);
     ';

	if (false == Db::getInstance()->execute($sql)) {
		return false;
	}
	$tabId = Tab::getIdFromClassName('AdminMollieModule');
	$tab = new Tab($tabId);
	$tab->name = MultiLangUtility::createMultiLangField('Mollie');
	$tab->icon = 'mollie';
	$tab->active = true;
	$tab->update();

	return true;
}
