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

use Cart;
use Context;
use Mollie\Repository\PendingOrderCartRepository;
use MolPendingOrderCart;
use Order;

class RestorePendingCartService
{
	private $repository;

	public function __construct(PendingOrderCartRepository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @throws \PrestaShopException
	 */
	public function restore(Order $order)
	{
		/** @var MolPendingOrderCart|null $pendingOrder */
		$pendingOrder = $this->repository->findOneBy([
			'order_id' => $order->id,
		]);

		if (!$pendingOrder) {
			return;
		}

		$cart = new Cart($pendingOrder->cart_id);

		$context = Context::getContext();
		$context->cookie->__set('id_cart', $cart->id);
		$context->cart = $cart;
		$context->cookie->write();
	}
}
