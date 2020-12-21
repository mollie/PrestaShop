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

namespace Mollie\Handler\CartRule;

use Cart;

interface CartRuleQuantityResetHandlerInterface
{
	/**
	 * @param Cart $cart
	 * @param array $cartRules
	 */
	public function handle(Cart $cart, $cartRules = []);
}
