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

use Mollie\Handler\OrderTotal\OrderTotalUpdaterHandlerInterface;

if (!defined('_PS_VERSION_')) {
	exit;
}

/**
 * @param Mollie $module
 *
 * @return bool
 */
function upgrade_module_4_2_3($module)
{
	/** @var OrderTotalUpdaterHandlerInterface $orderTotalRestrictionService */
	$orderTotalRestrictionService = $module->getMollieContainer(OrderTotalUpdaterHandlerInterface::class);

	try {
		$orderTotalRestrictionService->handleOrderTotalUpdate();
	} catch (Exception $e) {
		return false;
	}

	return true;
}
