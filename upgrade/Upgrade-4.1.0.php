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
 * @codingStandardsIgnoreStart
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
function upgrade_module_4_1_0($module)
{
	/** @var Installer $installer */
	$installer = $module->getMollieContainer(Installer::class);

	$awaitingOrderStatusId = Configuration::get(Config::STATUS_MOLLIE_AWAITING);
	$orderStatus = new OrderState($awaitingOrderStatusId);

	if (!Validate::isLoadedObject($orderStatus) || $orderStatus->deleted) {
		$installer->createAwaitingMollieOrderState();
	}

	$sql = '
        ALTER TABLE ' . _DB_PREFIX_ . 'mol_payment_method
        ADD `position` INT(10);
    ';

	$isAdded = Db::getInstance()->execute($sql);

	if (!$isAdded) {
		return false;
	}

	/** @var \Mollie\Repository\PaymentMethodRepositoryInterface $paymentMethodsRepo */
	$paymentMethodsRepo = $module->getMollieContainer(\Mollie\Repository\PaymentMethodRepositoryInterface::class);
	$paymentMethods = $paymentMethodsRepo->findAll();

	/** @var Installer $installer */
	$installer = $module->getMollieContainer(Installer::class);
	$installer->installVoucherFeatures();

	foreach ($installer::getHooks() as $hook) {
		$module->registerHook($hook);
	}

	$isUpdated = true;
	// adding positions for all payments in order they exist in database
	$iteration = 0;
	/** @var MolPaymentMethod $paymentMethod */
	foreach ($paymentMethods as $paymentMethod) {
		$paymentMethod->position = $iteration;

		++$iteration;

		$isUpdated &= $paymentMethod->update();
	}

	return $isUpdated;
}
