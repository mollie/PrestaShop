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

		if ($api_payment->method == "banktransfer")
		{
			if (isset($api_payment->metadata->cart_id))
			{
				// Possible failure because of old modules. So we check if order exists. if not, validateOrder
				$order_id = Order::getOrderByCartId($api_payment->metadata->cart_id);
				if (!$order_id)
				{
					$this->module->validateOrder(
						(int) $api_payment->metadata->cart_id,
						$this->module->statuses[$api_payment->status],
						$this->_convertEuroToCartCurrency($api_payment->amount,(int) $api_payment->metadata->cart_id),
						$api_payment->method,
						NULL,
						array(),
						NULL,
						FALSE,
						$api_payment->metadata->secure_key
					);

					$this->save_order_transaction_id($api_payment->id);
				}
			}
			elseif (isset($api_payment->metadata->order_id))
			{
				$order_id = $api_payment->metadata->order_id;
				$this->module->setOrderStatus($order_id, $api_payment->status);
			}			
		}
		else
		{
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
						$this->_convertEuroToCartCurrency($api_payment->amount,(int) $api_payment->metadata->cart_id),
						$api_payment->method,
						NULL,
						array(),
						NULL,
						FALSE,
						$api_payment->metadata->secure_key
					);

					$this->save_order_transaction_id($api_payment->id);
					
				}

				$order_id = $this->module->currentOrder;
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
	 * Retrieves the OrderPayment object, created at validateOrder. And add transaction id.
	 * @param $transaction_id
	 * @return bool
	 */
	public function save_order_transaction_id($id)
	{
		// retrieve ALL payments of order. 
		// in the case of a cancel or expired on banktransfer, this will fire too.
		// if no OrderPayment objects is retrieved in the collection, do nothing.
		$collection = OrderPayment::getByOrderId($this->module->currentOrder);
		if (count($collection) > 0)
		{
			$order_payment = $collection[0];
			
			// for older versions (1.5) , we check if it hasn't been filled yet.
			if (!$order_payment->transaction_id)
			{
				$order_payment->transaction_id = $id;
				$order_payment->update();
			}
		}
	}


	/**
	 * @param $transaction_id
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

	/**
	 * Transforms euro prices from mollie back to the currency of the Cart (order)
	 * @param float $amount in euros
	 * @param int $cart_id
	 * @return float in the currency of the cart
	 */
	protected function _convertEuroToCartCurrency($amount, $cart_id)
	{
		$cart          = new Cart($cart_id);
		$currency_euro = Currency::getIdByIsoCode('EUR');

		if (!$currency_euro)
		{
			// No Euro currency available!
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . ' said: In order to use this module, you need to enable Euros as currency. Cart ID: ' . $cart_id, Mollie::CRASH);
			}
			die($this->module->lang['This payment method is only available for Euros.']);
		}

		if ($cart->id_currency !== $currency_euro)
		{
			// Convert euro currency to cart currency
			$amount = Tools::convertPriceFull($amount, Currency::getCurrencyInstance($currency_euro), Currency::getCurrencyInstance($cart->id_currency));
		}

		return round($amount, 2);
	}
}
