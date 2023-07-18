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

namespace Mollie\Handler\Order;

use Cart;
use Configuration;
use Mollie\Provider\PaymentFeeProviderInterface;
use Mollie\Service\OrderPaymentFeeService;
use Mollie\Service\PaymentMethodService;
use Order;
use OrderDetail;
use PrestaShop\Decimal\Number;

class OrderPaymentFeeHandler
{
    /** @var OrderPaymentFeeService */
    private $orderPaymentFeeService;
    /** @var PaymentMethodService */
    private $paymentMethodService;
    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;

    public function __construct(
        OrderPaymentFeeService $orderPaymentFeeService,
        PaymentMethodService $paymentMethodService,
        PaymentFeeProviderInterface $paymentFeeProvider
    ) {
        $this->orderPaymentFeeService = $orderPaymentFeeService;
        $this->paymentMethodService = $paymentMethodService;
        $this->paymentFeeProvider = $paymentFeeProvider;
    }

    public function addOrderPaymentFee(int $orderId, $apiPayment): int
    {
        $order = new Order($orderId);
        $cart = new Cart($order->id_cart);

        $originalAmountWithTax = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );

        $originalAmountWithoutTax = $cart->getOrderTotal(
            false,
            Cart::BOTH
        );

        $paymentMethod = $this->paymentMethodService->getPaymentMethod($apiPayment);

        $paymentFeeData = $this->paymentFeeProvider->getPaymentFee($paymentMethod, (float) $originalAmountWithTax);

        $this->orderPaymentFeeService->createOrderPaymentFee($orderId, (int) $order->id_cart, $paymentFeeData);

        $order = new Order($orderId);

        $order->total_paid_tax_excl = (float) (new Number((string) $originalAmountWithoutTax))->plus((new Number((string) $paymentFeeData->getPaymentFeeTaxExcl())))->toPrecision(2);
        $order->total_paid_tax_incl = (float) (new Number((string) $originalAmountWithTax))->plus((new Number((string) $paymentFeeData->getPaymentFeeTaxIncl())))->toPrecision(2);
        $order->total_paid = (float) $apiPayment->amount->value;
        $order->total_paid_real = (float) $apiPayment->amount->value;

        $order->update();

        return $orderId;
    }

    private function isOrderBackOrder($orderId)
    {
        $order = new Order($orderId);
        $orderDetails = $order->getOrderDetailList();
        /** @var OrderDetail $detail */
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (
                Configuration::get('PS_STOCK_MANAGEMENT') &&
                ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock < 0)
            ) {
                return true;
            }
        }

        return false;
    }
}
