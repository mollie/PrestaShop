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
use Mollie\Repository\ReadOnlyRepositoryInterface;
use MolPendingOrderCart;
use Order;

/**
 * Memorizes the cart.
 */
class MemorizeCartService
{
	private $orderCartAssociationService;
	private $pendingOrderCartRepository;

	public function __construct(
		OrderCartAssociationService $orderCartAssociationService,
		ReadOnlyRepositoryInterface $pendingOrderCartRepository
	) {
		$this->orderCartAssociationService = $orderCartAssociationService;
		$this->pendingOrderCartRepository = $pendingOrderCartRepository;
	}

	public function memorizeCart(Order $toBeProcessedOrder)
	{
		// create a pending cart so we can repeat the process once again
		$this->orderCartAssociationService->createPendingCart($toBeProcessedOrder);
	}

	public function removeMemorizedCart(Order $successfulProcessedOrder)
	{
		/** @var MolPendingOrderCart|null $pendingOrderCart */
		$pendingOrderCart = $this->pendingOrderCartRepository->findOneBy([
			'order_id' => $successfulProcessedOrder->id,
		]);

		if (null === $pendingOrderCart) {
			return;
		}

		$cart = new Cart($pendingOrderCart->cart_id);

		if (!\Validate::isLoadedObject($cart)) {
			return;
		}

		$cart->delete();
	}
}
