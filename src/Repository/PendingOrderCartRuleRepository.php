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

use Db;
use MolPendingOrderCartRule;
use Order;
use OrderCartRule;

final class PendingOrderCartRuleRepository extends AbstractRepository implements PendingOrderCartRuleRepositoryInterface
{
	public function __construct()
	{
		parent::__construct(MolPendingOrderCartRule::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function removePreviousPendingOrderCartRule($orderId, $cartRuleId)
	{
		Db::getInstance()->delete('mol_pending_order_cart_rule',
			'id_order= ' . (int) $orderId . ' AND id_cart_rule= ' . (int) $cartRuleId
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createPendingOrderCartRule($orderId, $cartRuleId, OrderCartRule $orderCartRule)
	{
		if (empty($orderCartRule)) {
			return;
		}

		$pendingOrderCartRule = new MolPendingOrderCartRule();
		$pendingOrderCartRule->name = $orderCartRule->name;
		$pendingOrderCartRule->id_order = (int) $orderId;
		$pendingOrderCartRule->id_cart_rule = (int) $cartRuleId;
		$pendingOrderCartRule->id_order_invoice = (int) $orderCartRule->id_order_invoice;
		$pendingOrderCartRule->free_shipping = (bool) $orderCartRule->free_shipping;
		$pendingOrderCartRule->value_tax_excl = $orderCartRule->value_tax_excl;
		$pendingOrderCartRule->value_tax_incl = $orderCartRule->value;

		$pendingOrderCartRule->add();
	}

	/**
	 * {@inheritDoc}
	 */
	public function usePendingOrderCartRule(Order $order, MolPendingOrderCartRule $pendingOrderCartRule)
	{
		if (empty($pendingOrderCartRule)) {
			return;
		}

		$order->addCartRule(
			$pendingOrderCartRule->id_cart_rule,
			$pendingOrderCartRule->name,
			[
				'tax_incl' => $pendingOrderCartRule->value_tax_incl,
				'tax_excl' => $pendingOrderCartRule->value_tax_excl,
			],
			$pendingOrderCartRule->id_order_invoice,
			$pendingOrderCartRule->free_shipping
		);
	}
}
