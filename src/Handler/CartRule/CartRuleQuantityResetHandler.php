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
use Order;
use OrderCartRule;

class CartRuleQuantityResetHandler implements CartRuleQuantityResetHandlerInterface
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
	public function handle(Cart $cart, $cartRules = [], $paymentSuccessful = false)
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

			/** @var OrderCartRule|null $orderCartRule */
			$orderCartRule = $this->orderCartRuleRepository->findOneBy([
				'id_order' => (int) $order->id,
				'id_cart_rule' => (int) $cartRule->id,
			]);

			if (empty($orderCartRule)) {
				continue;
			}

			/* Reseting quantities on memoization allows to duplicate cart rules by passing cart rule validation */
			$this->resetQuantities($order->id, $cartRule, $orderCartRule);
		}
	}

	/**
	 * @param int $orderId
	 * @param CartRule $cartRule
	 * @param OrderCartRule $orderCartRule
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	private function resetQuantities($orderId, CartRule $cartRule, OrderCartRule $orderCartRule)
	{
		$this->increaseAvailableCartRuleQuantity($cartRule);
		$this->decreaseCustomerUsedCartRuleQuantity($orderId, $cartRule, $orderCartRule);
	}

	/**
	 * @param int $orderId
	 * @param CartRule $cartRule
	 * @param OrderCartRule $orderCartRule
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	private function decreaseCustomerUsedCartRuleQuantity($orderId, CartRule $cartRule, OrderCartRule $orderCartRule)
	{
		$this->pendingOrderCartRuleRepository->removePreviousPendingOrderCartRule($orderId, $cartRule->id);
		$this->pendingOrderCartRuleRepository->createPendingOrderCartRule($orderId, $cartRule->id, $orderCartRule);
		$this->orderCartRuleRepository->decreaseCustomerUsedCartRuleQuantity($orderId, $cartRule->id);
	}

	/**
	 * @param CartRule $cartRule
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	private function increaseAvailableCartRuleQuantity(CartRule $cartRule)
	{
		$cartRule->quantity = $cartRule->quantity + 1;
		$cartRule->update();
	}
}
