<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Handler\CartRule;

use Cart;
use CartRule;
use Mollie\Config\Config;
use Mollie\Repository\OrderCartRuleRepository;
use Mollie\Repository\PendingOrderCartRuleRepository;
use Order;

class CartRuleHandler implements CartRuleHandlerInterface
{
	/**
	 * @var OrderCartRuleRepository
	 */
	private $orderCartRuleRepository;

	/**
	 * @var PendingOrderCartRuleRepository
	 */
	private $pendingOrderCartRuleRepository;

	public function __construct(
		OrderCartRuleRepository $orderCartRuleRepository,
		PendingOrderCartRuleRepository $pendingOrderCartRuleRepository
	) {
		$this->orderCartRuleRepository = $orderCartRuleRepository;
		$this->pendingOrderCartRuleRepository = $pendingOrderCartRuleRepository;
	}

	public function handle(Cart $cart, $backtraceLocation, $paymentSuccess = false, $cartRules = [])
	{
		if ($backtraceLocation === Config::RESTORE_CART_BACKTRACE_MEMORIZATION_SERVICE) {
			$this->resetQuantities($cart, $cartRules);
		}

		if ($paymentSuccess) {
			$this->setQuantities($cart, $cartRules);
		}
	}

	/**
	 * To duplicate cart rules quantities must be reset to pass validation (Cart rules for new cart are created before removing from previous cart by PS)
	 *
	 * @param Cart $cart
	 * @param array $cartRules
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	public function resetQuantities(Cart $cart, $cartRules = [])
	{
		if (empty($cartRules)) {
			return;
		}
		$order = Order::getByCartId($cart->id); /** @phpstan-ignore-line */

		foreach ($cartRules as $cartRuleContent) {
			$cartRule = new CartRule($cartRuleContent['id_cart_rule']);
			$orderCartRule = $this->orderCartRuleRepository->getOrderCartRule($order, $cartRule);

			$this->increaseAvailableCartRuleQuantity($cartRule);
			$this->pendingOrderCartRuleRepository->removePreviousPendingOrderCartRule($order, $cartRule);
			$this->pendingOrderCartRuleRepository->createPendingOrderCartRule($order, $cartRule, $orderCartRule);
			$this->orderCartRuleRepository->decreaseCustomerUsedCartRuleQuantity($order, $cartRule);
		}
	}

	/**
	 * @param Cart $cart
	 * @param array $cartRules
	 *
	 * @throws \PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	public function setQuantities(Cart $cart, $cartRules = [])
	{
		if (empty($cartRules)) {
			return;
		}
		$order = Order::getByCartId($cart->id); /** @phpstan-ignore-line */

		foreach ($cartRules as $cartRuleContent) {
			$cartRule = new CartRule($cartRuleContent['id_cart_rule']);
			$orderCartRuleData = $this->pendingOrderCartRuleRepository->getPendingOrderCartRule($order, $cartRule);

			$this->decreaseAvailableCartRuleQuantity($cartRule);
			$this->pendingOrderCartRuleRepository->usePendingOrderCartRule($order, $orderCartRuleData);
			$this->pendingOrderCartRuleRepository->removePreviousPendingOrderCartRule($order, $cartRule);
		}
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
