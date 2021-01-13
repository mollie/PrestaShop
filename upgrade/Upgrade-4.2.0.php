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
use Mollie\Exception\OrderTotalRestrictionException;
use Mollie\Handler\OrderTotal\OrderTotalUpdaterHandlerInterface;
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
	/** @var Mollie\Tracker\Segment $segment */
	$segment = $module->getMollieContainer(Mollie\Tracker\Segment::class);

	$segment->setMessage('Mollie upgrade 4.2.0');
	$segment->track();

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

	Db::getInstance()->execute(' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mol_payment_method_order_total_restriction` (
            `id_payment_method_order_total_restriction` INT(64) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `id_payment_method` INT(10) NOT NULL,
            `currency_iso` VARCHAR(64) NOT NULL,
            `minimum_order_total` decimal(20,6),
            `maximum_order_total` decimal(20,6)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
	);

	/** @var OrderTotalUpdaterHandlerInterface $orderTotalRestrictionService */
	$orderTotalRestrictionService = $module->getMollieContainer(OrderTotalUpdaterHandlerInterface::class);
	try {
		$orderTotalRestrictionService->handleOrderTotalUpdate();
	} catch (OrderTotalRestrictionException $e) {
		//Do nothing as most likely mollie is not configured.
	}

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
