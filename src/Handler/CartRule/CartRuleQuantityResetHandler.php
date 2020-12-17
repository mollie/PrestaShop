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
use Mollie\Repository\CartRuleRepository;
use Mollie\Repository\OrderCartRuleRepository;
use Mollie\Repository\OrderRepository;
use Mollie\Repository\PendingOrderCartRuleRepository;
use Order;
use OrderCartRule;

class CartRuleQuantityResetHandler implements CartRuleHandlerInterface
{
    /**
     * @var PendingOrderCartRuleRepository
     */
    private $pendingOrderCartRuleRepository;

    /**
     * @var OrderCartRuleRepository
     */
    private $orderCartRuleRepository;

    /**
     * @var CartRuleRepository
     */
    private $cartRuleRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(
        PendingOrderCartRuleRepository $pendingOrderCartRuleRepository,
        OrderCartRuleRepository $orderCartRuleRepository,
        CartRuleRepository $cartRuleRepository,
        OrderRepository $orderRepository
    ) {
        $this->pendingOrderCartRuleRepository = $pendingOrderCartRuleRepository;
        $this->orderCartRuleRepository = $orderCartRuleRepository;
        $this->cartRuleRepository = $cartRuleRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function handle(Cart $cart, $cartRules = [])
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneByCartId($cart->id);

        if (empty($order)) {
            return;
        }

        foreach ($cartRules as $cartRuleContent) {
            /** @var CartRule $cartRule */
            $cartRule = $this->cartRuleRepository->findOneBy(
                ['id_cart_rule' => (int)$cartRuleContent['id_cart_rule']]
            );

            if (empty($cartRule)) {
                continue;
            }

            /** @var OrderCartRule $orderCartRule */
            $orderCartRule = $this->orderCartRuleRepository->findOneBy([
                'id_order' => (int)$order->id,
                'id_cart_rule' => (int)$cartRule->id
            ]);

            if (empty($orderCartRule)) {
                continue;
            }

            /** Reseting quantities on memoization allows to duplicate cart rules by passing cart rule validation */
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
>>>>>>> Stashed changes
