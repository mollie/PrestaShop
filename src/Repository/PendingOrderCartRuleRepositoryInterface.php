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

namespace Mollie\Repository;

use MolPendingOrderCartRule;
use Order;
use OrderCartRule;

interface PendingOrderCartRuleRepositoryInterface extends ReadOnlyRepositoryInterface
{
	/**
	 * @param int $orderId
	 * @param int $cartRuleId
	 */
	public function removePreviousPendingOrderCartRule($orderId, $cartRuleId);

	/**
	 * Used to create MolPendingOrderCartRule from OrderCartRule to be used later on successful payment to increase customer used cart rule quantity.
	 *
	 * @param int $orderId
	 * @param int $cartRuleId
	 * @param OrderCartRule $orderCartRule
	 */
	public function createPendingOrderCartRule($orderId, $cartRuleId, OrderCartRule $orderCartRule);

	/**
	 * Used to create OrderCartRule from MolPendingOrderCartRule
	 *
	 * @param Order $order
	 * @param MolPendingOrderCartRule $pendingOrderCartRule
	 */
	public function usePendingOrderCartRule(Order $order, MolPendingOrderCartRule $pendingOrderCartRule);
}
