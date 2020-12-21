<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://github.com/mollie/PrestaShop
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Cart;
use CartRule;
use Context;
use Mollie;
use Mollie\Config\Config;
use Mollie\Handler\CartRule\CartRuleQuantityChangeHandlerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\OrderStatusUtility;
use MolliePrefix\Mollie\Api\Types\OrderStatus;
use Order;
use OrderDetail;

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
	 * @var CartRuleQuantityChangeHandlerInterface
	 */
	private $cartRuleQuantityChangeHandlerInterface;

	public function __construct(
		Mollie $module,
		CartDuplicationService $cartDuplicationService,
		PaymentMethodRepository $paymentMethodRepository,
		RepeatOrderLinkFactory $orderLinkFactory,
		TransactionService $transactionService,
		CartRuleQuantityChangeHandlerInterface $cartRuleQuantityChangeHandlerInterface
	) {
		$this->module = $module;
		$this->context = Context::getContext();
		$this->cartDuplicationService = $cartDuplicationService;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->orderLinkFactory = $orderLinkFactory;
		$this->transactionService = $transactionService;
		$this->cartRuleQuantityChangeHandlerInterface = $cartRuleQuantityChangeHandlerInterface;
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
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

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
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

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
		$this->cartRuleQuantityChangeHandlerInterface->handle($cart, $cartRules);

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
