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

use Mollie\Service\TransactionService;
use Mollie\Utility\TransactionUtility;
use MolliePrefix\Mollie\Api\Exceptions\ApiException;
use PrestaShop\PrestaShop\Adapter\CoreException;

if (!defined('_PS_VERSION_')) {
	exit;
}

require_once dirname(__FILE__) . '/../../mollie.php';

class MollieWebhookModuleFrontController extends ModuleFrontController
{
	/** @var Mollie */
	public $module;
	/** @var bool */
	public $ssl = true;
	/** @var bool */
	public $display_column_left = false;
	/** @var bool */
	public $display_column_right = false;

	/**
	 * Prevent displaying the maintenance page.
	 *
	 * @return void
	 */
	protected function displayMaintenancePage()
	{
	}

	/**
	 * @throws ApiException
	 * @throws CoreException
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	public function initContent()
	{
		if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG)) {
			PrestaShopLogger::addLog('Mollie incoming webhook: ' . Tools::file_get_contents('php://input'));
		}

		exit($this->executeWebhook());
	}

	/**
	 * @return string
	 *
	 * @throws ApiException
	 * @throws CoreException
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 */
	protected function executeWebhook()
	{
		if (Tools::getValue('testByMollie')) {
			if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
				PrestaShopLogger::addLog(__METHOD__ . ' said: Mollie webhook tester successfully communicated with the shop.', Mollie\Config\Config::NOTICE);
			}

			return 'OK';
		}
		/** @var TransactionService $transactionService */
		$transactionService = $this->module->getContainer(TransactionService::class);

		$transactionId = Tools::getValue('id');
		if (TransactionUtility::isOrderTransaction($transactionId)) {
			$payment = $transactionService->processTransaction($this->module->api->orders->get($transactionId, ['embed' => 'payments']));
		} else {
			$payment = $transactionService->processTransaction($this->module->api->payments->get($transactionId));
		}
		if (is_string($payment)) {
			return $payment;
		}

		return 'OK';
	}
}
