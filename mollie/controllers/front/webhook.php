<?php

/**
 * Copyright (c) 2012-2014, Mollie B.V.
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
 * @category    Mollie
 * @package     Mollie
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @link        https://www.mollie.nl
 */

if (!defined('_PS_VERSION_'))
{
	die('No direct script access');
}

/**
 * Class MollieReturnModuleFrontController
 * @method setTemplate
 * @property mixed context
 * @property Mollie module
 */

class MollieWebhookModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		echo $this->_executeWebhook();
		exit;
	}


	/**
	 * @return string
	 */
	protected function _executeWebhook()
	{
		if (Tools::getValue('testByMollie'))
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . 'said: Mollie webhook tester successfully communicated with the shop.', Mollie::NOTICE);
			}
			return 'OK';
		}

		$transaction_id = Tools::getValue('id');

		if (empty($transaction_id))
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . 'said: Received webhook request without proper transaction ID.', Mollie::WARNING);
			}
			return 'NO ID';
		}

		try
		{
			/** @var Mollie_API_Object_Payment $api_payment */
			$api_payment  = $this->module->api->payments->get($transaction_id);
			$transaction_id = $api_payment->id;
		}
		catch (Exception $e)
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . 'said: Could not retrieve payment details for transaction_id "' . $transaction_id . '". Reason: ' . $e->getMessage(), Mollie::WARNING);
			}
			return 'NOT OK';
		}

		$ps_payment = $this->module->getPaymentBy('transaction_id', $transaction_id);

		if (isset($api_payment->metadata->cart_id))
		{
			if (
				 $ps_payment['bank_status'] === Mollie_API_Object_Payment::STATUS_OPEN &&
				 $api_payment->status === Mollie_API_Object_Payment::STATUS_PAID )
			{
				// Misnomer ahead: think of validateOrder as "createOrderFromCart"
				$this->module->validateOrder(
					(int) $api_payment->metadata->cart_id,
					$this->module->statuses[$api_payment->status],
					$api_payment->amount,
					$api_payment->method
				);

				$order_id = $this->module->currentOrder;
			}
		}

		/**
		 * Older versions tie payments to orders, and create a cart upon payment creation.
		 * In order to support the transition between these two cases we check for the
		 * occurrence of order_id in the metadata. In these cases we only update the order status
		 */

		elseif (isset($api_payment->metadata->order_id))
		{

			$order_id = $api_payment->metadata->order_id;

			$this->module->setOrderStatus($order_id, $api_payment->status);
		}

		// Store status in database
		if (!$this->_savePaymentStatus($transaction_id, $api_payment->status))
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . 'said: Could not save Mollie payment status for transaction "' . $transaction_id . '". Reason: ' . Db::getInstance()->getMsgError(), Mollie::WARNING);
			}
		}

		// Log successful webhook requests in extended log mode only
		if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ALL)
		{
			Logger::addLog(__METHOD__ . 'said: Received webhook request for order ' . (int) $order_id . ' / transaction ' . $transaction_id, Mollie::NOTICE);
		}
		return 'OK';
	}


	/**
	 * @param $order_id
	 * @param $status
	 * @return bool
	 */
	protected function _savePaymentStatus($transaction_id, $status)
	{
		$data = array(
			'updated_at' => date("Y-m-d H:i:s"),
			'bank_status' => $status,
		);

		return Db::getInstance()->update('mollie_payments', $data, '`transaction_id` = \'' . Db::getInstance()->escape($transaction_id) . '\'');
	}
}
