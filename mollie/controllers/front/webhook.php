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
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG'))
			{
				Logger::addLog(__METHOD__ . 'said: Mollie webhook tester successfully communicated with the shop.', Mollie::NOTICE);
			}
			return 'OK';
		}

		$id = Tools::getValue('id');

		if (empty($id))
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG'))
			{
				Logger::addLog(__METHOD__ . 'said: Received webhook request without proper transaction ID.', Mollie::WARNING);
			}
			return 'NO ID';
		}

		try
		{
			/** @var Mollie_API_Object_Payment $payment */
			$payment  = $this->module->api->payments->get($id);
			$order_id = $payment->metadata->order_id;
			$status   = $payment->status;
		}
		catch (Exception $e)
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG'))
			{
				Logger::addLog(__METHOD__ . 'said: Could not retrieve payment details for id "' . $id . '". Reason: ' . $e->getMessage(), Mollie::WARNING);
			}
			return 'NOT OK';
		}

		// Store status in database
		if (!$this->_saveOrderStatus($order_id, $status))
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG'))
			{
				Logger::addLog(__METHOD__ . 'said: Could not save order status for payment "' . $id . '". Reason: ' . Db::getInstance()->getMsgError(), Mollie::WARNING);
			}
		}

		// Tell status to Shop
		$this->module->setOrderStatus($order_id, $status);

		// Log successful webhook requests in extended log mode only
		if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') > 1)
		{
			Logger::addLog(__METHOD__ . 'said: Received webhook request for order ' . (int) $order_id . ' / transaction ' . htmlentities($id), Mollie::NOTICE);
		}
		return 'OK';
	}


	/**
	 * @param $order_id
	 * @param $status
	 * @return bool
	 */
	protected function _saveOrderStatus($order_id, $status)
	{
		$data = array(
			'updated_at' => date("Y-m-d H:i:s"),
			'bank_status' => $status,
		);

		return Db::getInstance()->update('mollie_payments', $data, '`order_id` = ' . (int)$order_id);
	}
}
