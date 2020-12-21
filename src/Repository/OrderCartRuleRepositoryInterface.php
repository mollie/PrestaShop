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

interface OrderCartRuleRepositoryInterface extends ReadOnlyRepositoryInterface
{
	/**
	 * @param int $orderId
	 * @param int $cartRuleId
	 *
	 * @return bool
	 */
	public function decreaseCustomerUsedCartRuleQuantity($orderId, $cartRuleId);
}
