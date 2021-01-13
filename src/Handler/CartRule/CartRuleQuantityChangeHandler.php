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
use CartRule;
use Mollie\Repository\CartRuleRepositoryInterface;
use Mollie\Repository\OrderCartRuleRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Repository\PendingOrderCartRuleRepositoryInterface;
use MolPendingOrderCartRule;
use Order;

class CartRuleQuantityChangeHandler implements CartRuleQuantityChangeHandlerInterface
{
	/**
	 * @var PendingOrderCartRuleRepositoryInterface
	 */
	private $pendingOrderCartRuleRepository;

	/**
	 * @var OrderCartRuleRepositoryInterface
	 */
	private $orderCartRuleRepository;

	/**
	 * @var CartRuleRepositoryInterface
	 */
	private $cartRuleRepository;

	/**
	 * @var OrderRepositoryInterface
	 */
	private $orderRepository;

	public function __construct(
		PendingOrderCartRuleRepositoryInterface $pendingOrderCartRuleRepository,
		OrderCartRuleRepositoryInterface $orderCartRuleRepository,
		CartRuleRepositoryInterface $cartRuleRepository,
		OrderRepositoryInterface $orderRepository
	) {
		$this->pendingOrderCartRuleRepository = $pendingOrderCartRuleRepository;
		$this->orderCartRuleRepository = $orderCartRuleRepository;
		$this->cartRuleRepository = $cartRuleRepository;
		$this->orderRepository = $orderRepository;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(Cart $cart, $cartRules = [])
	{
		/** @var Order|null $order */
		$order = $this->orderRepository->findOneByCartId($cart->id);

		if (empty($order)) {
			return;
		}

		foreach ($cartRules as $cartRuleContent) {
			/** @var CartRule|null $cartRule */
			$cartRule = $this->cartRuleRepository->findOneBy(
				['id_cart_rule' => (int) $cartRuleContent['id_cart_rule']]
			);

			if (empty($cartRule)) {
				continue;
			}
			/** @var MolPendingOrderCartRule|null $pendingOrderCartRule */
			$pendingOrderCartRule = $this->pendingOrderCartRuleRepository->findOneBy([
				'id_order' => (int) $order->id,
				'id_cart_rule' => (int) $cartRule->id,
			]);

			if (empty($pendingOrderCartRule)) {
				continue;
			}

			/* On successful payment decrease quantities because it is only done on initialization of payment (First cart) */
			$this->setQuantities($order, $cartRule, $pendingOrderCartRule);
		}
	}

	/**
	 * @param Order $order
	 * @param CartRule $cartRule
	 * @param MolPendingOrderCartRule $pendingOrderCartRule
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	private function setQuantities(Order $order, CartRule $cartRule, $pendingOrderCartRule)
	{
		$this->decreaseAvailableCartRuleQuantity($cartRule);
		$this->increaseCustomerUsedCartRuleQuantity($order, $cartRule, $pendingOrderCartRule);
	}

	/**
	 * @param CartRule $cartRule
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	private function decreaseAvailableCartRuleQuantity(CartRule $cartRule)
	{
		$cartRule->quantity = max(0, $cartRule->quantity - 1);
		$cartRule->update();
	}

	/**
	 * @param Order $order
	 * @param CartRule $cartRule
	 * @param MolPendingOrderCartRule $pendingOrderCartRule
	 */
	private function increaseCustomerUsedCartRuleQuantity(Order $order, CartRule $cartRule, MolPendingOrderCartRule $pendingOrderCartRule)
	{
		$this->pendingOrderCartRuleRepository->usePendingOrderCartRule($order, $pendingOrderCartRule);
		$this->pendingOrderCartRuleRepository->removePreviousPendingOrderCartRule($order->id, $cartRule->id);
	}
}
