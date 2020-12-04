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
use MolliePrefix\Mollie\Api\Exceptions\ApiException;
use MolliePrefix\Mollie\Api\Resources\Order;
use MolliePrefix\Mollie\Api\Resources\Payment;
use PrestaShop\PrestaShop\Adapter\CoreException;
use PrestaShopDatabaseException;
use PrestaShopException;
use SmartyException;
use Tools;

class CancelService
{
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
	 * @param string $transactionId
	 * @param array $lines
	 *
	 * @return array
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 * @throws CoreException
	 * @throws SmartyException
	 *
	 * @since 3.3.0
	 */
	public function doCancelOrderLines($transactionId, $lines = [])
	{
		try {
			/** @var Order $payment */
			$order = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
			if ([] === $lines) {
				$order->cancel();
			} else {
				$cancelableLines = [];
				foreach ($lines as $line) {
					$cancelableLines[] = ['id' => $line['id'], 'quantity' => $line['quantity']];
				}
				$order->cancelLines(['lines' => $cancelableLines]);
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
				'message' => $this->module->l('The product(s) could not be canceled!'),
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
