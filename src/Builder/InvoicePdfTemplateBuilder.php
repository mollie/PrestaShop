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
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Builder;

use Cart;
use Currency;
use Mollie\Repository\OrderFeeRepository;
use MolOrderFee;
use Order;
use Tools;

final class InvoicePdfTemplateBuilder implements TemplateBuilderInterface
{
    private $orderFeeRepository;

    /**
     * @var Order
     */
    private $order;

    public function __construct(OrderFeeRepository $orderFeeRepository)
    {
        $this->orderFeeRepository = $orderFeeRepository;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    public function buildParams()
    {
        $orderFeeId = $this->orderFeeRepository->getOrderFeeIdByCartId(Cart::getCartIdByOrderId($this->order->id));

        $orderFee = new MolOrderFee($orderFeeId);

        if (!$orderFee->order_fee) {
            return [];
        }

        return [
            'orderFeeAmountDisplay' => Tools::displayPrice(
                $orderFee->order_fee,
                new Currency($this->order->id_currency)
            )
        ];
    }
}
