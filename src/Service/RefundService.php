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

use Mollie;
use Mollie\Utility\EnvironmentUtility;
use Mollie\Utility\RefundUtility;
use Mollie\Utility\TextFormatUtility;
use MolliePrefix\Mollie\Api\Exceptions\ApiException;
use MolliePrefix\Mollie\Api\Resources\Order as MollieOrderAlias;
use MolliePrefix\Mollie\Api\Resources\Payment;
use MolliePrefix\Mollie\Api\Resources\PaymentCollection;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;

class RefundService
{
	const FILE_NAME = 'RefundService';

	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var TransactionService
	 */
	private $transactionService;

	public function __construct(Mollie $module, TransactionService $transactionService)
	{
		$this->module = $module;
		$this->transactionService = $transactionService;
	}

	/**
	 * @param string $transactionId Transaction/Mollie Order ID
	 * @param float|null $amount Amount to refund, refund all if `null`
	 *
	 * @return array
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 * @throws ApiException
	 *
	 * @since 3.3.0 Renamed `doRefund` to `doPaymentRefund`, added `$amount`
	 * @since 3.3.2 Omit $orderId
	 */
	public function doPaymentRefund($transactionId, $amount = null)
	{
		try {
			/** @var Payment $payment */
			$payment = $this->module->api->payments->get($transactionId);
			if ($amount) {
				$payment->refund([
					'amount' => [
						'currency' => (string) $payment->amount->currency,
						'value' => (string) TextFormatUtility::formatNumber($amount, 2),
					],
				]);
			} elseif ((float) $payment->settlementAmount->value - (float) $payment->amountRefunded->value > 0) {
				$payment->refund([
					'amount' => [
						'currency' => (string) $payment->amount->currency,
						'value' => (string) TextFormatUtility::formatNumber(
							RefundUtility::getRefundableAmount(
								(float) $payment->settlementAmount->value,
								(float) RefundUtility::getRefundedAmount(iterator_to_array($payment->refunds()))
							),
							2
						),
					],
				]);
			}
		} catch (ApiException $e) {
			return [
				'status' => 'fail',
				'msg_fail' => $this->module->l('The order could not be refunded!', self::FILE_NAME),
				'msg_details' => $this->module->l('Reason:', self::FILE_NAME) . ' ' . $e->getMessage(),
			];
		}

		if (EnvironmentUtility::isLocalEnvironment()) {
			// Refresh payment on local environments
			/** @var Payment $payment */
			$apiPayment = $this->module->api->payments->get($transactionId);
			if (!Tools::isSubmit('module')) {
				$_GET['module'] = $this->module->name;
			}
			$this->transactionService->processTransaction($apiPayment);
		}

		return [
			'status' => 'success',
			'msg_success' => $this->module->l('The order has been refunded!', self::FILE_NAME),
			'msg_details' => $this->module->l('Mollie B.V. will transfer the money back to the customer on the next business day.', self::FILE_NAME),
		];
	}

	/**
	 * @param array $lines
	 *
	 * @return array
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.3.0
	 */
	public function doRefundOrderLines(array $orderData, $lines = [])
	{
		$transactionId = $orderData['id'];
		$availableRefund = $orderData['availableRefundAmount'];
		try {
			/** @var MollieOrderAlias $payment */
			$order = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
			$isOrderLinesRefundPossible = RefundUtility::isOrderLinesRefundPossible($lines, $availableRefund);
			if ($isOrderLinesRefundPossible) {
				$refund = RefundUtility::getRefundLines($lines);
				$order->refund($refund);
			} else {
				/** @var PaymentCollection $orderPayments */
				$orderPayments = $order->payments();
				/** @var \MolliePrefix\Mollie\Api\Resources\Payment $orderPayment */
				foreach ($orderPayments as $orderPayment) {
					$orderPayment->refund(
						[
							'amount' => $availableRefund,
						]
					);
					continue;
				}
			}

			if (EnvironmentUtility::isLocalEnvironment()) {
				// Refresh payment on local environments
				/** @var Payment $payment */
				$apiPayment = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
				if (!Tools::isSubmit('module')) {
					$_GET['module'] = $this->module->name;
				}
				$this->transactionService->processTransaction($apiPayment);
			}
		} catch (ApiException $e) {
			return [
				'success' => false,
				'message' => $this->module->l('The product(s) could not be refunded!', self::FILE_NAME),
				'detailed' => $e->getMessage(),
			];
		}

		return [
			'success' => true,
			'message' => '',
			'detailed' => '',
		];
	}
}
