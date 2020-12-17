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
use MolPendingOrderCartRule;
use Order;

class CartRuleQuantityChangeHandler implements CartRuleHandlerInterface
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
        $order = $this->orderRepository->findOneByCartId($cart->id);

        if (empty($order)) {
            return;
        }

        foreach ($cartRules as $cartRuleContent) {
            $cartRule = $this->cartRuleRepository->findOneBy(
                ['id_cart_rule' => (int)$cartRuleContent['id_cart_rule']]
            );

            if (empty($cartRule)) {
                continue;
            }

            $pendingOrderCartRule = $this->pendingOrderCartRuleRepository->findOneBy([
                'id_order' => (int)$order->id,
                'id_cart_rule' => (int)$cartRule->id
            ]);

            if (empty($pendingOrderCartRule)) {
                continue;
            }

            /** On successful payment decrease quantities because it is only done on initialization of payment (First cart) */
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