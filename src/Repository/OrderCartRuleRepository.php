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
use OrderCartRule;

final class OrderCartRuleRepository extends AbstractRepository implements OrderCartRuleRepositoryInterface
{
	public function __construct()
	{
		parent::__construct(OrderCartRule::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function decreaseCustomerUsedCartRuleQuantity($orderId, $cartRuleId)
	{
		return (bool) Db::getInstance()->delete(
			'order_cart_rule',
			'id_order= ' . (int) $orderId . ' AND id_cart_rule= ' . (int) $cartRuleId,
			1
		);
	}
}
