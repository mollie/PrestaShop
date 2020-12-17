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

use CartRule;
use Db;
use DbQuery;
use Order;

final class PendingOrderCartRuleRepository
{
    /**
     * @param Order $order
     * @param CartRule $cartRule
     *
     * @return array
     */
    public function getPendingOrderCartRule($order, CartRule $cartRule)
    {
        $dbQuery = new DbQuery();
        $dbQuery->select('*');
        $dbQuery->from('mol_pending_order_cart_rule');
        $dbQuery->where('id_order= ' . (int) $order->id . ' AND id_cart_rule= ' . (int) $cartRule->id);

        return Db::getInstance()->getRow($dbQuery);
    }

    /**
     * @param Order $order
     * @param CartRule $cartRule
     */
    public function removePreviousPendingOrderCartRule($order, CartRule $cartRule)
    {
        Db::getInstance()->delete('mol_pending_order_cart_rule',
            'id_order= ' . (int) $order->id . ' AND id_cart_rule= ' . (int) $cartRule->id
        );
    }

    /**
     * Creating pending order cart rule to be used later on successful payment.
     *
     * @param Order $order
     * @param CartRule $cartRule
     * @param array $orderCartRule
     *
     * @throws \PrestaShopDatabaseException
     */
    public function createPendingOrderCartRule($order, CartRule $cartRule, $orderCartRule)
    {
        if (empty($orderCartRule)) {
            return;
        }

        Db::getInstance()->insert('mol_pending_order_cart_rule', [
            'id_order' => (int) $order->id,
            'id_cart_rule' => (int) $cartRule->id,
            'name' => $orderCartRule['name'],
            'value_tax_incl' => $orderCartRule['value_tax_incl'] ?: 0,
            'value_tax_excl' => $orderCartRule['value_tax_excl'] ?: 0,
            'free_shipping' => (int) $orderCartRule['free_shipping'] ?: 0,
            'id_order_invoice' => (int) $orderCartRule['id_order_invoice'] ?: 0,
        ]);
    }
}