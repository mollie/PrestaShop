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

namespace Mollie\Service;

use Address;
use Carrier;
use Cart;
use Configuration;
use MolKlarnaInvoice;
use Mollie\Repository\ShippedProductRepository;
use MolShippedProducts;
use Order;
use OrderDetail;
use OrderInvoice;
use Tools;

class ShippedProductService
{
    /**
     * @var ShippedProductRepository
     */
    private $shippedProductRepository;

    public function __construct(ShippedProductRepository $shippedProductRepository)
    {
        $this->shippedProductRepository = $shippedProductRepository;
    }

    public function createShippedProducts($orderId, $shipment)
    {
        foreach ($shipment->lines as $line) {
            $metaData = json_decode($line->metadata);
            $quantity = $line->quantity;
            $unitPrice = $line->unitPrice;
            $totalAmount = $line->totalAmount;

            $order = new Order($orderId);
            $invoiceNumber = $this->createOrderInvoice($order);

            $molShippedProducts = new MolShippedProducts();
            $molShippedProducts->shipment_id = $shipment->id;
            $molShippedProducts->mollie_order_id = $line->orderId;
            $molShippedProducts->product_id = $metaData->product_id;
            $molShippedProducts->quantity = $quantity;
            $molShippedProducts->unit_price = $unitPrice->value;
            $molShippedProducts->total_amount = $totalAmount->value;
            $molShippedProducts->currency = $totalAmount->currency;
            $molShippedProducts->order_id = $orderId;
            $molShippedProducts->invoice_number = $invoiceNumber;

            $molShippedProducts->add();

            $klarnaInvoice = new MolKlarnaInvoice();
            $klarnaInvoice->shipment_id = $shipment->id;
            $klarnaInvoice->is_created = false;

            $klarnaInvoice->add();

            $metaData = json_decode($line->metadata);
            $productId = $metaData->product_id;

            $orderDetailList = $order->getOrderDetailList();
            foreach ($orderDetailList as $orderDetail) {
                if ($orderDetail['product_id'] !== $productId) {
                    continue;
                }
                $invoice = OrderInvoice::getInvoiceByNumber($invoiceNumber);

                $orderDetailObj = new OrderDetail($orderDetail['id_order_detail']);
                $orderDetailObj->product_quantity -= $line->quantity;
                $orderDetailObj->total_price_tax_incl =
                    $orderDetailObj->unit_price_tax_incl * $orderDetailObj->product_quantity;
                $orderDetailObj->total_price_tax_excl =
                    $orderDetailObj->unit_price_tax_excl * $orderDetailObj->product_quantity;
                if ($orderDetailObj->product_quantity <= 0) {
                    $orderDetailObj->delete();
                } else {
                    $orderDetailObj->update();
                }

                $orderDetailObj->id_order_detail = null;
                $orderDetailObj->product_quantity = $line->quantity;
                $orderDetailObj->total_price_tax_incl =
                    $orderDetailObj->unit_price_tax_incl * $line->quantity;
                $orderDetailObj->total_price_tax_excl =
                    $orderDetailObj->unit_price_tax_excl * $line->quantity;
                $orderDetailObj->id_order_invoice = $invoice->id;
                $orderDetailObj->add();
            }
        }
    }
    public function createOrderInvoice(Order $order)
    {
        $shippedProducts = $this->shippedProductRepository->findBy(
            [
                'order_id' => $order->id
            ]
        );
        $cart = new Cart($order->id_cart);
//        $transaction = $this->api->orders->get($transactionId, ['embed' => 'payments']);

        $use_taxes = true;

        $total_method = Cart::BOTH;
        $invoice_address = new Address((int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE', null, null, $order->id_shop)});
        $carrier = new Carrier((int) $order->id_carrier);
        $tax_calculator = $carrier->getTaxCalculator($invoice_address);

        $order_invoice = new OrderInvoice();
        $order_invoice->id_order = $order->id;
        $order_invoice->number = Order::getLastInvoiceNumber() + 1;
        $order_invoice->total_paid_tax_excl = Tools::ps_round((float) $cart->getOrderTotal(false, $total_method), 2);
        $order_invoice->total_paid_tax_incl = Tools::ps_round((float) $cart->getOrderTotal($use_taxes, $total_method), 2);
        $order_invoice->total_products = (float) $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $order_invoice->total_products_wt = (float) $cart->getOrderTotal($use_taxes, Cart::ONLY_PRODUCTS);
        $order_invoice->total_shipping_tax_excl = (float) $cart->getTotalShippingCost(null, false);
        $order_invoice->total_shipping_tax_incl = (float) $cart->getTotalShippingCost();

        $order_invoice->total_wrapping_tax_excl = abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING));
        $order_invoice->total_wrapping_tax_incl = abs($cart->getOrderTotal($use_taxes, Cart::ONLY_WRAPPING));
        $order_invoice->shipping_tax_computation_method = (int) $tax_calculator->computation_method;

        $order_invoice->add();

//        $order_detail = new OrderDetail();
//        $order_detail->createList($order, $cart, $order->getCurrentOrderState(), $cart->getProducts(), (isset($order_invoice) ? $order_invoice->id : 0), $use_taxes, (int) Tools::getValue('add_product_warehouse'));

        foreach ($order->getOrderPayments() as $orderPayment) {
            Db::getInstance()->execute('
                            INSERT INTO `' . _DB_PREFIX_ . 'order_invoice_payment`
                            SET
                                `id_order_invoice` = ' . (int)$order_invoice->id . ',
                                `id_order_payment` = ' . (int)$orderPayment->id . ',
                                `id_order` = ' . (int)$order_invoice->id_order);
        }

        return $order_invoice->number;

    }

}