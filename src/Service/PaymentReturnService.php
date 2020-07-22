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

use Cart;
use Context;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepository;
use Order;
use OrderDetail;
use OrderPayment;

class PaymentReturnService
{
    const PENDING = 1;
    const DONE = 2;

    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var CartDuplicationService
     */
    private $cartDuplicationService;

    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;

    /**
     * @var RepeatOrderLinkFactory
     */
    private $orderLinkFactory;


    public function __construct(
        Mollie $module,
        CartDuplicationService $cartDuplicationService,
        PaymentMethodRepository $paymentMethodRepository,
        RepeatOrderLinkFactory $orderLinkFactory
    ) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->cartDuplicationService = $cartDuplicationService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderLinkFactory = $orderLinkFactory;
    }

    public function handlePendingStatus(Order $order, $transaction, $orderStatus, $paymentMethod, $stockManagement)
    {
        $cart = new Cart($order->id_cart);
        $status = static::PENDING;
        $orderDetails = $order->getOrderDetailList();
        /** @var OrderDetail $detail */
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (
                $stockManagement &&
                ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock < 0)
            ) {
                $orderStatus = Config::STATUS_PENDING_ON_BACKORDER;
                break;
            }
        }

        $this->updateTransactions($transaction->id, $order->id, $orderStatus, $paymentMethod);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
    }

    public function handlePaidStatus(Order $order, $transaction, $orderStatus, $paymentMethod, $stockManagement)
    {
        $cart = new Cart($order->id_cart);
        $status = static::DONE;
        $orderDetails = $order->getOrderDetailList();
        /** @var OrderDetail $detail */
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (
                $stockManagement &&
                ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock < 0)
            ) {
                $orderStatus = Mollie\Config\Config::STATUS_PAID_ON_BACKORDER;
                break;
            }
        }
        $orderStatus = Mollie\Utility\OrderStatusUtility::transformPaymentStatusToRefunded($transaction);
        $this->updateTransactions($transaction->id, $order->id, $orderStatus, $paymentMethod);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
    }

    public function handleFailedStatus(Order $order, $transaction, $orderStatus, $paymentMethod)
    {
        if(null !== $paymentMethod) {

            $this->cartDuplicationService->restoreCart($order->id_cart);

            $warning[] = $this->module->l('Your payment was not successful, please try again.');

            $this->context->cookie->mollie_payment_canceled_error =
                json_encode($warning);

            $this->updateTransactions($transaction->id, $order->id, $orderStatus, $paymentMethod);
        }

        $orderLink = $this->orderLinkFactory->getLink();

        return [
            'success' => true,
            'status' => static::DONE,
            'response' => json_encode($transaction),
            'href' => $orderLink
        ];
    }

    private function getStatusResponse($transaction, $status, $cartId, $cartSecureKey)
    {
        $successUrl = $this->context->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => (int)$cartId,
                'id_module' => (int)$this->module->id,
                'id_order' => (int)version_compare(_PS_VERSION_, '1.7.1.0', '>=')
                    ? Order::getIdByCartId((int)$cartId)
                    : Order::getOrderByCartId((int)$cartId),
                'key' => $cartSecureKey,
            ]
        );

        return [
            'success' => true,
            'status' => $status,
            'response' => json_encode($transaction),
            'href' => $successUrl
        ];
    }

    private function updateTransactions($transactionId, $orderId, $orderStatus, $paymentMethod)
    {
        /** @var OrderStatusService $orderStatusService */
        $orderStatusService = $this->module->getContainer(OrderStatusService::class);

        $orderStatusId = (int)Mollie\Config\Config::getStatuses()[$orderStatus];
        $this->paymentMethodRepository->savePaymentStatus($transactionId, $orderStatus, $orderId, $paymentMethod);

        $order = new Order($orderId);
        $order->payment = $paymentMethod;
        $order->update();

        $transactionInfo = [
            'transactionId' => $transactionId,
            'paymentMethod' => $paymentMethod,
        ];
        $orderStatusService->setOrderStatus($orderId, $orderStatusId, null, [], $transactionInfo);

        $orderPayments = OrderPayment::getByOrderId($orderId);
        /** @var OrderPayment $orderPayment */
        foreach ($orderPayments as $orderPayment) {
            $orderPayment->transaction_id = $transactionId;
            $orderPayment->payment_method = $paymentMethod;
            $orderPayment->update();
        }
    }
}
