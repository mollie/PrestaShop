<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 */

use Mollie\Config\Config;
use Mollie\Install\Installer;
use Mollie\Service\OrderStateImageService;

if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_2_0($module)
{
	/** @var Installer $installer */
	$installer = $module->getMollieContainer(Installer::class);

	$installer->klarnaPaymentAuthorizedState();
	$installer->klarnaPaymentShippedState();

	$acceptedStatusId = Configuration::get(Config::MOLLIE_STATUS_KLARNA_AUTHORIZED);
	Configuration::updateValue(Config::MOLLIE_KLARNA_INVOICE_ON, $acceptedStatusId);

	$module->registerHook('actionOrderGridQueryBuilderModifier');
	$module->registerHook('actionOrderGridDefinitionModifier');

	Db::getInstance()->execute(' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_pending_order_cart_rule` (
            `id_mol_pending_order_cart_rule` INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `id_order` VARCHAR(64) NOT NULL,
            `id_cart_rule` VARCHAR(64) NOT NULL,
            `name` VARCHAR(64) NOT NULL,
            `value_tax_incl` decimal(20,6) NOT NULL,
            `value_tax_excl` decimal(20,6) NOT NULL,
            `free_shipping` TINYINT(1) NOT NULL,
            `id_order_invoice` INT(64) NOT NULL
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
	);

	/**
	 * @var OrderStateImageService $imageService
	 */
	$imageService = $module->getMollieContainer(OrderStateImageService::class);
	$mollieOrderStatuses = Config::getMollieOrderStatuses();

	foreach ($mollieOrderStatuses as $mollieOrderStatus) {
		$orderStatusId = Configuration::get($mollieOrderStatus);

		if ($orderStatusId) {
			$imageService->deleteOrderStateLogo($orderStatusId);
			$imageService->deleteTemporaryOrderStateLogo($orderStatusId);
			$imageService->createOrderStateLogo($orderStatusId);
			$imageService->createTemporaryOrderStateLogo($orderStatusId);
		}
	}

	return true;
}
