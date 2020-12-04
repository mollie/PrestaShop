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

use Configuration;
use Mollie\Config\Config;
use Mollie\Utility\OrderStatusUtility;
use Order;
use OrderHistory;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Validate;

class OrderStatusService
{
	/**
	 * @var MailService
	 */
	private $mailService;

	public function __construct(MailService $mailService)
	{
		$this->mailService = $mailService;
	}

	/**
	 * @param int $order
	 * @param string|int $statusId
	 * @param null $useExistingPayment
	 * @param array $templateVars
	 *
	 * @return void
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.3.2 Accept both Order ID and Order object
	 * @since 3.3.2 Accept both Mollie status string and PrestaShop status ID
	 * @since 3.3.2 $useExistingPayment option
	 * @since 3.3.4 Accepts template vars for the corresponding email template
	 */
	public function setOrderStatus($order, $statusId, $useExistingPayment = null, $templateVars = [])
	{
		if (is_string($statusId)) {
			$status = $statusId;
			if (empty(Config::getStatuses()[$statusId])) {
				return;
			}
			$statusId = (int) Config::getStatuses()[$statusId];
		} else {
			$status = '';
			foreach (Config::getStatuses() as $mollieStatus => $prestaShopStatusId) {
				if ((int) $prestaShopStatusId === $statusId) {
					$status = $mollieStatus;
					break;
				}
			}
		}

		if (0 === (int) $statusId) {
			return;
		}

		if (!$order instanceof Order) {
			$order = new Order((int) $order);
		}

		if (!Validate::isLoadedObject($order)) {
			return;
		}

		if ((int) $order->current_state === (int) $statusId) {
			return;
		}

		if (!Validate::isLoadedObject($order)
			|| !$status
		) {
			return;
		}
		if (null === $useExistingPayment) {
			$useExistingPayment = !$order->hasInvoice();
		}

		$history = new OrderHistory();
		$history->id_order = $order->id;
		$history->changeIdOrderState($statusId, $order, $useExistingPayment);

		$status = OrderStatusUtility::transformPaymentStatusToPaid($status, Config::STATUS_PAID_ON_BACKORDER);

		if ($this->checkIfOrderConfNeedsToBeSend($statusId)) {
			$this->mailService->sendOrderConfMail($order, $statusId);
		}

		if ($this->checkIfNewOrderMailNeedsToBeSend($statusId)) {
			$this->mailService->sendNewOrderMail($order, $statusId);
		}

		if ('0' === Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($status))) {
			$history->add();
		} else {
			$history->addWithemail(true, $templateVars);
		}
	}

	private function checkIfOrderConfNeedsToBeSend($statusId)
	{
		if (Config::NEW_ORDER_MAIL_SEND_ON_PAID !== (int) Configuration::get(Config::MOLLIE_SEND_ORDER_CONFIRMATION)) {
			return false;
		}

		return ((int) $statusId === (int) Configuration::get(Config::MOLLIE_STATUS_PAID)) ||
			((int) $statusId === (int) Configuration::get(Config::STATUS_PS_OS_OUTOFSTOCK_PAID));
	}

	private function checkIfNewOrderMailNeedsToBeSend($statusId)
	{
		if (Config::NEW_ORDER_MAIL_SEND_ON_PAID !== (int) Configuration::get(Config::MOLLIE_SEND_NEW_ORDER)) {
			return false;
		}

		return ((int) $statusId === (int) Configuration::get(Config::MOLLIE_STATUS_PAID)) ||
			((int) $statusId === (int) Configuration::get(Config::STATUS_PS_OS_OUTOFSTOCK_PAID));
	}
}
