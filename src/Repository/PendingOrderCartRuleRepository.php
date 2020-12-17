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

namespace Mollie\Repository;

use Db;
use MolPendingOrderCartRule;
use Order;
use OrderCartRule;

final class PendingOrderCartRuleRepository extends AbstractRepository
{
    /**
     * @param int $orderId
     * @param int $cartRuleId
     */
	public function removePreviousPendingOrderCartRule($orderId, $cartRuleId)
	{
		Db::getInstance()->delete('mol_pending_order_cart_rule',
			'id_order= ' . (int) $orderId . ' AND id_cart_rule= ' . (int) $cartRuleId
		);
	}

    /**
     * Creating pending order cart rule to be used later on successful payment.
     *
     * @param int $orderId
     * @param int $cartRuleId
     * @param OrderCartRule $orderCartRule
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
	public function createPendingOrderCartRule($orderId, $cartRuleId, OrderCartRule $orderCartRule)
    {
        if (empty($orderCartRule)) {
            return;
        }

        $pendingOrderCartRule = new MolPendingOrderCartRule();
        $pendingOrderCartRule->name = $orderCartRule->name;
        $pendingOrderCartRule->id_order = (int) $orderId;
        $pendingOrderCartRule->id_cart_rule = (int) $cartRuleId;
        $pendingOrderCartRule->id_order_invoice = (int) $orderCartRule->id_order_invoice;
        $pendingOrderCartRule->free_shipping = (int) $orderCartRule->free_shipping;
        $pendingOrderCartRule->value_tax_excl = $orderCartRule->value_tax_excl;
        $pendingOrderCartRule->value_tax_incl = $orderCartRule->value;

        $pendingOrderCartRule->add();
    }

	public function usePendingOrderCartRule(Order $order, MolPendingOrderCartRule $pendingOrderCartRule)
	{
		if (empty($pendingOrderCartRule)) {
			return;
		}

		$order->addCartRule(
            $pendingOrderCartRule->id_cart_rule,
			$pendingOrderCartRule->name,
			[
			    'tax_incl' => $pendingOrderCartRule->value_tax_incl,
                'tax_excl' => $pendingOrderCartRule->value_tax_excl
            ],
			$pendingOrderCartRule->id_order_invoice,
			$pendingOrderCartRule->free_shipping
        );
	}
}
