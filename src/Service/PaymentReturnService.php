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

namespace Mollie\Service;

use Cart;
use CartRule;
use Context;
use Mollie;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\OrderStatusUtility;
use MolliePrefix\Mollie\Api\Types\OrderStatus;
use Order;
use OrderDetail;
use Mollie\Handler\CartRule\CartRuleQuantityResetHandler;
use Mollie\Handler\CartRule\CartRuleQuantityChangeHandler;

class PaymentReturnService
{
	const PENDING = 1;
	const DONE = 2;
	const FILE_NAME = 'PaymentReturnService';

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

	/**
	 * @var TransactionService
	 */
	private $transactionService;

    /**
     * @var CartRuleQuantityResetHandler
     */
    private $cartRuleQuantityResetHandler;

    /**
     * @var CartRuleQuantityChangeHandler
     */
    private $cartRuleQuantityChangeHandler;


    public function __construct(
		Mollie $module,
		CartDuplicationService $cartDuplicationService,
		PaymentMethodRepository $paymentMethodRepository,
		RepeatOrderLinkFactory $orderLinkFactory,
		TransactionService $transactionService,
        CartRuleQuantityResetHandler $cartRuleQuantityResetHandler,
        CartRuleQuantityChangeHandler $cartRuleQuantityChangeHandler
	) {
		$this->module = $module;
		$this->context = Context::getContext();
		$this->cartDuplicationService = $cartDuplicationService;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->orderLinkFactory = $orderLinkFactory;
		$this->transactionService = $transactionService;
        $this->cartRuleQuantityResetHandler = $cartRuleQuantityResetHandler;
        $this->cartRuleQuantityChangeHandler = $cartRuleQuantityChangeHandler;
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
		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandler->handle($cart, $cartRules);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handlePaidStatus(Order $order, $transaction, $paymentMethod, $stockManagement)
	{
		$cart = new Cart($order->id_cart);
		$status = static::DONE;
		$orderStatus = OrderStatusUtility::transformPaymentStatusToRefunded($transaction);
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

		if (!$order->getOrderPayments()) {
			$this->transactionService->updateOrderTransaction($transaction->id, $order->reference);
		}

		$this->updateTransactions($transaction->id, $order->id, $orderStatus, $paymentMethod);
		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandler->handle($cart, $cartRules);

		return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handleAuthorizedStatus(Order $order, $transaction, $paymentMethod, $stockManagement)
	{
		$cart = new Cart($order->id_cart);
		$status = static::DONE;
		$orderStatus = OrderStatusUtility::transformPaymentStatusToRefunded($transaction);
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
		$this->updateTransactions($transaction->id, $order->id, $orderStatus, $paymentMethod);
		/* @phpstan-ignore-next-line */
		$cartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        $this->cartRuleQuantityChangeHandler->handle($cart, $cartRules);

        return $this->getStatusResponse($transaction, $status, $cart->id, $cart->secure_key);
	}

	public function handleFailedStatus(Order $order, $transaction, $orderStatus, $paymentMethod)
	{
		if (null !== $paymentMethod) {
			$this->cartDuplicationService->restoreCart($order->id_cart, Config::RESTORE_CART_BACKTRACE_RETURN_CONTROLLER);

			$warning[] = $this->module->l('Your payment was not successful, please try again.', self::FILE_NAME);

			$this->context->cookie->__set('mollie_payment_canceled_error', json_encode($warning));

			$this->updateTransactions($transaction->id, $order->id, $orderStatus, $paymentMethod);
		}

		$orderLink = $this->orderLinkFactory->getLink();

		return [
			'success' => true,
			'status' => static::DONE,
			'response' => json_encode($transaction),
			'href' => $orderLink,
		];
	}

	private function getStatusResponse($transaction, $status, $cartId, $cartSecureKey)
	{
		/* @phpstan-ignore-next-line */
		$orderId = (int) Order::getOrderByCartId((int) $cartId);

		$successUrl = $this->context->link->getPageLink(
			'order-confirmation',
			true,
			null,
			[
				'id_cart' => (int) $cartId,
				'id_module' => (int) $this->module->id,
				'id_order' => $orderId,
				'key' => $cartSecureKey,
			]
		);

		return [
			'success' => true,
			'status' => $status,
			'response' => json_encode($transaction),
			'href' => $successUrl,
		];
	}

	private function updateTransactions($transactionId, $orderId, $orderStatus, $paymentMethod)
	{
		/** @var OrderStatusService $orderStatusService */
		$orderStatusService = $this->module->getMollieContainer(OrderStatusService::class);

		$orderStatusId = (int) Mollie\Config\Config::getStatuses()[$orderStatus];
		$this->paymentMethodRepository->savePaymentStatus($transactionId, $orderStatus, $orderId, $paymentMethod);
		$isKlarnaOrder = in_array($paymentMethod, Config::KLARNA_PAYMENTS, false);
		if (OrderStatus::STATUS_COMPLETED === $orderStatus && $isKlarnaOrder) {
			$orderStatusId = (int) Config::getStatuses()[Config::MOLLIE_STATUS_KLARNA_SHIPPED];
		}
		$order = new Order($orderId);
		$order->payment = $paymentMethod;
		$order->update();

		$orderStatusService->setOrderStatus($orderId, $orderStatusId, null, []);
	}
}
