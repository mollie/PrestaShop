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

namespace Mollie\Service;

use Mollie\Config\Config;
use MolPendingOrderCart;
use Order;

class OrderCartAssociationService
{
	private $cartDuplication;

	public function __construct(CartDuplicationService $cartDuplication)
	{
		$this->cartDuplication = $cartDuplication;
	}

	/**
	 * @return bool
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	public function createPendingCart(Order $order)
	{
		// globally restores the cart.
		$newCartId = $this->cartDuplication->restoreCart($order->id_cart, Config::RESTORE_CART_BACKTRACE_MEMORIZATION_SERVICE);

		$pendingOrderCart = new MolPendingOrderCart();
		$pendingOrderCart->cart_id = $newCartId;
		$pendingOrderCart->order_id = $order->id;

		return $pendingOrderCart->add();
	}
}
