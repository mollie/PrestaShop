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
use Mollie\Install\Installer;

if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_0_7($module)
{
	Configuration::updateValue(Config::MOLLIE_STATUS_SHIPPING, Configuration::get('PS_OS_SHIPPING'));
	Configuration::updateValue(Config::MOLLIE_STATUS_SHIPPING, true);
	Configuration::updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, Config::ORDER_CONF_MAIL_SEND_ON_NEVER);

	$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_excluded_country` (
				`id_mol_country`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`id_method`       VARCHAR(64),
				`id_country`      INT(64),
				`all_countries` tinyint
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

	$sql .= '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart` (
				`id_mol_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
				`order_id` INT(64) NOT NULL,
				`cart_id` INT(64) NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ';

	if (false == Db::getInstance()->execute($sql)) {
		return false;
	}

	$module->registerHook('actionAdminOrdersListingFieldsModifier');
	$module->registerHook('actionAdminControllerSetMedia');
	$module->registerHook('actionValidateOrder');

	/** @var Installer $installer */
	$installer = $module->getMollieContainer(Installer::class);
	$installed = true;

	$installed &= $installer->installTab('AdminMollieAjax', 0, 'AdminMollieAjax', false);
	$installed &= $installer->installTab('AdminMollieModule', 'IMPROVE', 'Mollie', false, 'mollie');

	$installed &= $installer->createPartialShippedOrderState();
	$installed &= $installer->createOrderCompletedOrderState();
	$installed &= $installer->copyEmailTemplates();

	Configuration::updateValue(
		Config::MOLLIE_STATUS_COMPLETED,
		Configuration::get(Config::MOLLIE_STATUS_ORDER_COMPLETED)
	);
	Configuration::updateValue(Config::MOLLIE_MAIL_WHEN_COMPLETED, true);

	if (!$installed) {
		return false;
	}

	return true;
}
