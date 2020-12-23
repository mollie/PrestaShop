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

namespace Mollie\Service;

use Cart;
use CartRule;
use Context;
use Mollie\Config\Config;
use Mollie\Handler\CartRule\CartRuleQuantityResetHandlerInterface;

class CartDuplicationService
{
	/**
	 * @var CartRuleDuplicationService
	 */
	private $cartRuleDuplicationService;

	/**
	 * @var CartRuleQuantityResetHandlerInterface
	 */
	private $cartRuleQuantityResetHandlerInterface;

	public function __construct(
		CartRuleDuplicationService $cartRuleDuplicationService,
		CartRuleQuantityResetHandlerInterface $cartRuleQuantityResetHandlerInterface
	) {
		$this->cartRuleDuplicationService = $cartRuleDuplicationService;
		$this->cartRuleQuantityResetHandlerInterface = $cartRuleQuantityResetHandlerInterface;
	}

	/**
	 * @param int $cartId
	 * @param string $backtraceLocation
	 *
	 * @return int
	 *
	 * @throws \Exception
	 */
	public function restoreCart($cartId, $backtraceLocation)
	{
		$context = Context::getContext();
		$cart = new Cart($cartId);
		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);

		if ($backtraceLocation === Config::RESTORE_CART_BACKTRACE_MEMORIZATION_SERVICE) {
			$this->cartRuleQuantityResetHandlerInterface->handle($cart, $cartRules);
		}
		$duplication = $cart->duplicate();
		if ($duplication['success']) {
			/** @var Cart $duplicatedCart */
			$duplicatedCart = $duplication['cart'];

			$context->cookie->__set('id_cart', $duplicatedCart->id);
			$context->cart = $duplicatedCart;
			$context->cookie->write();
			$this->cartRuleDuplicationService->restoreCartRules($cartRules);

			return $duplicatedCart->id;
		}

		return 0;
	}
}
