<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Address;
use Cart;
use Configuration;
use Context;
use Country;
use Currency;
use Db;
use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\OrderStatusUtility;
use Mollie\Utility\TransactionUtility;
use Order;
use OrderDetail;
use OrderPayment;
use PrestaShopDatabaseException;
use PrestaShopException;
use PrestaShopLogger;

class TransactionService
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var PaymentMethodRepository
	 */
	private $paymentMethodRepository;

	/**
	 * @var \Mollie\Service\OrderStatusService
	 */
	private $orderStatusService;

	/**
	 * @var Country
	 */
	private $country;

	public function __construct(
		Mollie $module,
		PaymentMethodRepository $paymentMethodRepository,
		OrderStatusService $orderStatusService
	) {
		$this->module = $module;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->orderStatusService = $orderStatusService;
		$this->country = Context::getContext()->country;
	}

	/**
	 * @param MolliePaymentAlias|MollieOrderAlias $transaction
	 *
	 * @return string|MolliePaymentAlias Returns a single payment (in case of Orders API it returns the highest prio Payment object) or status string
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 * @throws ApiException
	 *
	 * @since 3.3.0
	 * @since 3.3.2 Returns the ApiPayment / ApiOrder instead of OK string, NOT OK/NO ID stays the same
	 * @since 3.3.2 Returns the ApiPayment instead of ApiPayment / ApiOrder
	 */
	public function processTransaction($transaction)
	{
		if (empty($transaction)) {
			if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
				PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request without proper transaction ID.', Mollie\Config\Config::WARNING);
			}

			return $this->module->l('Transaction failed', 'webhook');
		}

		// Ensure that we are dealing with a Payment object, in case of transaction ID or Payment object w/ Order ID, convert
		if ($transaction instanceof MolliePaymentAlias) {
			if (!empty($transaction->orderId) && TransactionUtility::isOrderTransaction($transaction->orderId)) {
				// Part of order
				$transaction = $this->module->api->orders->get($transaction->orderId, ['embed' => 'payments']);
			} else {
				// Single payment
				$apiPayment = $transaction;
			}
		}

		if (!empty($transaction->id) && TransactionUtility::isOrderTransaction(($transaction->id))) {
			$apiPayment = $this->module->api->orders->get($transaction->id, ['embed' => 'payments']);
		}

		if (!isset($apiPayment)) {
			return $this->module->l('Transaction failed', 'webhook');
		}

		$this->setCountryContextIfNotSet($apiPayment);
		/** @var int $orderId */
		$orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
		/** @var OrderStatusService $orderStatusService */
		$orderStatusService = $this->module->getMollieContainer(OrderStatusService::class);
		$cart = new Cart($apiPayment->metadata->cart_id);

		Db::getInstance()->update(
			'mollie_payments',
			[
				'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
				'bank_status' => pSQL(\Mollie\Config\Config::getStatuses()[$apiPayment->status]),
				'order_id' => (int) $orderId,
			],
			'`transaction_id` = \'' . pSQL($transaction->id) . '\''
		);

		$key = Mollie\Utility\SecureKeyUtility::generateReturnKey(
			$cart->secure_key,
			$cart->id_customer,
			$cart->id,
			$this->module->name
		);
		switch ($transaction->resource) {
			case Mollie\Config\Config::MOLLIE_API_STATUS_PAYMENT:
				if ($apiPayment->metadata->cart_id) {
					if ($apiPayment->hasRefunds() || $apiPayment->hasChargebacks()) {
						if (isset($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
							&& \Mollie\Utility\NumberUtility::isLowerOrEqualThan($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
						) {
							$orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
						} else {
							$orderStatusService->setOrderStatus($orderId, Mollie\Config\Config::PARTIAL_REFUND_CODE);
						}
					} elseif ($key === $apiPayment->metadata->secure_key) {
						$paymentStatus = (int) Mollie\Config\Config::getStatuses()[$apiPayment->status];

						if (PaymentStatus::STATUS_PAID === $apiPayment->status) {
							$this->updateTransaction($orderId, $transaction);
						}
						if ($this->isOrderBackOrder($orderId)) {
							$paymentStatus = Mollie\Config\Config::STATUS_PAID_ON_BACKORDER;
						}
						/** @var OrderStatusService $orderStatusService */
						$orderStatusService = $this->module->getMollieContainer(OrderStatusService::class);
						$orderStatusService->setOrderStatus($orderId, $paymentStatus);

						$orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
					}
				}
				break;
			case Mollie\Config\Config::MOLLIE_API_STATUS_ORDER:
				if ($apiPayment->metadata->cart_id) {
					if ($key === $apiPayment->metadata->secure_key
						&& OrderStatus::STATUS_CREATED === $apiPayment->status
					) {
						/** @var PaymentCollection|null $orderPayments */
						$orderPayments = $apiPayment->payments();

						$paymentStatus = OrderStatus::STATUS_CREATED;
						foreach ($orderPayments as $orderPayment) {
							$paymentStatus = $orderPayment->status;
						}
						$paymentStatus = (int) Mollie\Config\Config::getStatuses()[$paymentStatus];

						/** @var OrderStatusService $orderStatusService */
						$orderStatusService = $this->module->getMollieContainer(OrderStatusService::class);
						$orderStatusService->setOrderStatus($orderId, $paymentStatus);

						$orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
					} elseif ($key === $apiPayment->metadata->secure_key) {
						$isKlarnaDefault = Configuration::get(Config::MOLLIE_KLARNA_INVOICE_ON) === Config::MOLLIE_STATUS_DEFAULT;
						$status = OrderStatusUtility::transformPaymentStatusToRefunded($apiPayment);
						$paymentStatus = (int) Config::getStatuses()[$status];
						$isKlarnaOrder = in_array($transaction->method, Config::KLARNA_PAYMENTS, false);
						if (OrderStatus::STATUS_COMPLETED === $status && $isKlarnaOrder && !$isKlarnaDefault) {
							$paymentStatus = (int) Config::getStatuses()[Config::MOLLIE_STATUS_KLARNA_SHIPPED];
						}
						if (PaymentStatus::STATUS_PAID === $status || OrderStatus::STATUS_AUTHORIZED === $status) {
							$this->updateTransaction($orderId, $transaction);
						}
						if ($this->isOrderBackOrder($orderId)) {
							$paymentStatus = Mollie\Config\Config::STATUS_PAID_ON_BACKORDER;
						}

						/** @var OrderStatusService $orderStatusService */
						$orderStatusService = $this->module->getMollieContainer(OrderStatusService::class);
						$orderStatusService->setOrderStatus($orderId, $paymentStatus, null, []);

						$orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
					}
				}
				break;
		}

		// Store status in database

		if (!$this->savePaymentStatus($transaction->id, $apiPayment->status, $orderId)) {
			if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
				PrestaShopLogger::addLog(__METHOD__ . ' said: Could not save Mollie payment status for transaction "' . $transaction->id . '". Reason: ' . Db::getInstance()->getMsgError(), Mollie\Config\Config::WARNING);
			}
		}

		// Log successful webhook requests in extended log mode only
		if (Mollie\Config\Config::DEBUG_LOG_ALL == Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG)) {
			PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request for order ' . (int) $orderId . ' / transaction ' . $transaction->id, Mollie\Config\Config::NOTICE);
		}

		return $apiPayment;
	}

	public function updateOrderTransaction($transactionId, $orderReference)
	{
		$transactionInfos = [];
		$isOrder = TransactionUtility::isOrderTransaction($transactionId);
		if ($isOrder) {
			$transaction = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
			/** @var PaymentCollection|null $payments */
			$payments = $transaction->payments();

			foreach ($payments as $payment) {
				if (Config::MOLLIE_VOUCHER_METHOD_ID === $transaction->method) {
					$transactionInfos = $this->getVoucherTransactionInfo($payment, $transactionInfos);
					$transactionInfos = $this->getVoucherRemainderTransactionInfo($payment, $transactionInfos);
				} else {
					$transactionInfos = $this->getPaymentTransactionInfo($payment, $transactionInfos);
				}
			}
		} else {
			$transaction = $this->module->api->payments->get($transactionId);
			$transactionInfos = $this->getPaymentTransactionInfo($transaction, $transactionInfos);
		}

		$this->updateOrderPayments($transactionInfos, $orderReference);
	}

	/**
	 * @param string $transactionId
	 * @param string $status
	 * @param int $orderId
	 *
	 * @return bool
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	protected function savePaymentStatus($transactionId, $status, $orderId)
	{
		try {
			return Db::getInstance()->update(
				'mollie_payments',
				[
					'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
					'bank_status' => pSQL($status),
					'order_id' => (int) $orderId,
				],
				'`transaction_id` = \'' . pSQL($transactionId) . '\''
			);
		} catch (PrestaShopDatabaseException $e) {
			/** @var PaymentMethodRepository $paymentMethodRepo */
			$paymentMethodRepo = $this->module->getMollieContainer(PaymentMethodRepository::class);
			$paymentMethodRepo->tryAddOrderReferenceColumn();
			throw $e;
		}
	}

	/**
	 * (Re)sets the controller country context.
	 * When Prestashop receives a call from Mollie (without context)
	 * Prestashop always has default context to fall back on, so context->country
	 * is allways Set before executing any controller methods.
	 *
	 * @param MolliePaymentAlias $payment
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws \PrestaShopException
	 */
	protected function setCountryContextIfNotSet($payment)
	{
		if (empty($this->country) || !$this->country->active) {
			if ($payment->metadata->cart_id) {
				$cart = new Cart((int) $payment->metadata->cart_id);
				if (!empty($cart)) {
					$address = new Address($cart->id_address_delivery);
					if (!empty($address)) {
						$country = new Country($address->id_country);
						if (!empty($country)) {
							$this->country = $country;
						}
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	private function getVoucherTransactionInfo(MolliePaymentAlias $payment, array $transactionInfos)
	{
		foreach ($payment->details->vouchers as $voucher) {
			$transactionInfos[] = [
				'paymentName' => $voucher->issuer,
				'amount' => $voucher->amount->value,
				'currency' => $voucher->amount->currency,
				'transactionId' => $payment->id,
			];
		}

		return $transactionInfos;
	}

	/**
	 * @return array
	 */
	private function getVoucherRemainderTransactionInfo(MolliePaymentAlias $payment, array $transactionInfos)
	{
		if ($payment->details->remainderMethod) {
			$transactionInfos[] = [
				'paymentName' => $payment->details->remainderMethod,
				'amount' => $payment->details->remainderAmount->value,
				'currency' => $payment->details->remainderAmount->currency,
				'transactionId' => $payment->id,
			];
		}

		return $transactionInfos;
	}

	/**
	 * @return array
	 */
	private function getPaymentTransactionInfo(MolliePaymentAlias $payment, array $transactionInfos)
	{
		$transactionInfos[] = [
			'paymentName' => $payment->method,
			'amount' => $payment->amount->value,
			'currency' => $payment->amount->currency,
			'transactionId' => $payment->id,
		];

		return $transactionInfos;
	}

	/**
	 * @param string $orderReference
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	private function updateOrderPayments(array $transactionInfos, $orderReference)
	{
		foreach ($transactionInfos as $transactionInfo) {
			$orderPayment = new OrderPayment();
			$orderPayment->order_reference = $orderReference;
			$orderPayment->amount = $transactionInfo['amount'];
			$orderPayment->payment_method = $transactionInfo['paymentName'];
			$orderPayment->transaction_id = $transactionInfo['transactionId'];
			$orderPayment->id_currency = Currency::getIdByIsoCode($transactionInfo['currency']);

			$orderPayment->add();
		}
	}

	/**
	 * @param int $orderId
	 * @param MolliePaymentAlias|MollieOrderAlias $transaction
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	private function updateTransaction($orderId, $transaction)
	{
		/** @var TransactionService $transactionService */
		$transactionService = $this->module->getMollieContainer(TransactionService::class);
		$order = new Order($orderId);
		if (!$order->getOrderPayments()) {
			$transactionService->updateOrderTransaction($transaction->id, $order->reference);
		}
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
