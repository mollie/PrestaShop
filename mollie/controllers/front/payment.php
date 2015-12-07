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
 * Class MolliePaymentModuleFrontController
 * @method setTemplate
 * @property mixed context
 * @property Mollie module
 */

class MolliePaymentModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		/** @var Cart $cart */
		$cart     = $this->context->cart;
		$customer = new Customer($cart->id_customer);

		if (!$this->_validate($cart, $customer))
		{
			die(
				$this->module->lang['This payment method is not available.'] .
				'<br /><br />' .
				'<a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . '">' .
				$this->module->lang['Click here to continue'] .
				'</a>.'
			);
		}

		$method = $_GET['method'];
		$issuer = !empty($_GET['issuer']) ? $_GET['issuer'] : NULL;

		// If no issuer was set yet and the issuer list has its own page, show issuer list here
		if ($issuer === null && $this->module->getConfigValue('MOLLIE_ISSUERS') === Mollie::ISSUERS_OWN_PAGE)
		{
			$tpl_data = array();
			$tpl_data['issuers'] = $this->_getIssuerList($method);

			// Only show issuers if a choice is available
			if (sizeof($tpl_data['issuers']) === 1)
			{
				$issuer = key($tpl_data['issuers']);
			}
			else if (!empty($tpl_data['issuers']))
			{
				$tpl_data['msg_bankselect'] = $this->module->lang['Select your bank:'];
				$tpl_data['msg_ok']         = $this->module->lang['OK'];
				$tpl_data['msg_return']     = $this->module->lang['Return to the homepage'];
				$tpl_data['module']         = $this->module;
				$this->context->smarty->assign($tpl_data);
				$this->setTemplate('mollie_issuers.tpl');
				return;
			}
		}

		// Currency conversion (thou shalt pay in euros)
		$orig_amount = $cart->getOrderTotal(TRUE, Cart::BOTH);
		$amount      = $this->_convertCurrencyToEuro($orig_amount);

		// Prepare payment
		$payment_data = $this->_getPaymentData($amount, $method, $issuer, (int) $cart->id, $customer->secure_key);
		$payment      = $this->_createPayment($payment_data);


		// Store payment linked to cart
		Db::getInstance()->insert(
			'mollie_payments',
			array(
				'cart_id'        => (int) $cart->id,
				'method'         => $payment->method,
				'transaction_id' => $payment->id,
				'bank_status'    => Mollie_API_Object_Payment::STATUS_OPEN,
				'created_at'     => date("Y-m-d H:i:s")
			)
		);

		if ($payment->method == "banktransfer")
		{
			$this->module->validateOrder(
					(int) $cart->id,
					$this->module->statuses[$payment->status],
					$orig_amount,
					$payment->method,
					NULL,
					array(),
					NULL,
					FALSE,
					$customer->secure_key
				);
		}

		// Go to payment url
		Tools::redirect($payment->getPaymentUrl());
	}

	/**
	 * Checks if this payment option is still available
	 * May redirect the user to a more appropriate page
	 *
	 * @param $cart
	 * @param $customer
	 * @return bool
	 */
	public function _validate($cart, $customer)
	{
		if (!$cart->id_customer ||
			!$cart->id_address_delivery ||
			!$cart->id_address_invoice ||
			!$this->module->active)
		{
			// We be like: how did you even get here?
			Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__);
			return FALSE;
		}

		$authorized = FALSE;

		foreach (Module::getPaymentModules() as $module)
		{
			if ($module['name'] == 'mollie')
			{
				$authorized = TRUE;
				break;
			}
		}

		if (!$authorized)
		{
			return FALSE;
		}

		if (!Validate::isLoadedObject($customer))
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Retrieves a list of issuers for the selected method
	 *
	 * @param string $method
	 * @return array
	 */
	protected function _getIssuerList($method)
	{
		try
		{
			$issuers = $this->module->api->issuers->all();
			$issuer_list = array();
			foreach ($issuers as $issuer)
			{
				if ($issuer->method === $method)
				{
					$issuer_list[$issuer->id] = $issuer->name;
				}
			}
			return $issuer_list;
		}
		catch (Mollie_API_Exception $e)
		{
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . ' said: ' . $e->getMessage(), Mollie::NOTICE);
			}
		}
		return array();
	}

	/**
	 * @param float $amount
	 * @return float
	 */
	protected function _convertCurrencyToEuro($amount)
	{
		$cart = $this->context->cart;
		$currency_euro = Currency::getIdByIsoCode('EUR');
		if (!$currency_euro)
		{
			// No Euro currency available!
			if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . ' said: In order to use this module, you need to enable Euros as currency.', Mollie::CRASH);
			}
			die($this->module->lang['This payment method is only available for Euros.']);
		}

		if ($cart->id_currency !== $currency_euro)
		{
			// Convert non-euro currency to default
			$amount = Tools::convertPrice($amount, $cart->id_currency, FALSE);

			if (Currency::getDefaultCurrency() !== $currency_euro)
			{
				// If default is not euro, convert to euro
				$amount = Tools::convertPrice($amount, $currency_euro, TRUE);
			}

		}

		return round($amount, 2);
	}

	/**
	 * @param int $cart_id
	 * @return string
	 */
	protected function _generateDescriptionFromCart($cart_id)
	{
		$cart = new Cart($cart_id);

		$buyer = null;
		if ($cart->id_customer)
		{
			$buyer = new Customer($cart->id_customer);
		}

		$filters = array(
			'cart.id' => $cart_id,
			'customer.firstname' => $buyer == null ? '' : $buyer->firstname,
			'customer.lastname' => $buyer == null ? '' : $buyer->lastname,
			'customer.company' => $buyer == null ? '' : $buyer->company,
		);
		
		$content = $this->module->getConfigValue('MOLLIE_DESCRIPTION');
		
		foreach($filters as $key => $value)
		{
			$content = str_replace("{".$key."}", $value, $content);
		}

		return $content;
	}

	/**
	 * @param float $amount
	 * @param string $method
	 * @param string|null $issuer
	 * @param int $cart_id
	 * @return array
	 */
	protected function _getPaymentData($amount, $method, $issuer, $cart_id, $secure_key)
	{
		$description = $this->_generateDescriptionFromCart($cart_id);

		$payment_data = array(
			"amount"      => $amount,
			"method"      => $method,
			"issuer"      => $issuer,
			"description" => str_replace('%', $cart_id, $description),
			"redirectUrl" => $this->context->link->getModuleLink('mollie','return', array('cart_id' => $cart_id, 'utm_nooverride' => 1)),
			"webhookUrl"  => $this->context->link->getModuleLink('mollie', 'webhook'),
			"metadata"    => array("cart_id" => $cart_id,"secure_key" => $secure_key)
		);

		// Send webshop locale
		if ($this->module->getConfigValue('MOLLIE_PAYMENTSCREEN_LOCALE') === Mollie::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE)
		{
			$locale = $this->_getWebshopLocale();

			if (preg_match('/^[a-z]{2}(?:[\-_][A-Z]{2})?$/iu', $locale))
			{
				$payment_data['locale'] = $locale;
			}
		}

		if (isset($this->context, $this->context->cart))
		{
			if (isset($this->context->cart->id_address_invoice))
			{
				$billing                        = new Address(intval($this->context->cart->id_address_invoice));
				$payment_data['billingCity']    = $billing->city;
				$payment_data['billingRegion']  = State::getNameById($billing->id_state);
				$payment_data['billingPostal']  = $billing->postcode;
				$payment_data['billingCountry'] = Country::getIsoById($billing->id_country);
			}
			if (isset($this->context->cart->id_address_delivery))
			{
				$shipping                        = new Address(intval($this->context->cart->id_address_delivery));
				$payment_data['shippingCity']    = $shipping->city;
				$payment_data['shippingRegion']  = State::getNameById($shipping->id_state);
				$payment_data['shippingPostal']  = $shipping->postcode;
				$payment_data['shippingCountry'] = Country::getIsoById($shipping->id_country);
			}
		}

		return $payment_data;
	}

	/**
	 * @return string
	 */
	protected function _getWebshopLocale ()
	{
		if ($this->context->language)
		{
			// Current language
			$language = $this->context->language->iso_code;
		}
		else
		{
			// Default locale language
			$language = Configuration::get('PS_LOCALE_LANGUAGE');
		}

		return strtolower($language).'_'.strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
	}

	/**
	 * @param array $data
	 * @return Mollie_API_Object_Payment|null
	 */
	protected function _createPayment($data)
	{
		$payment = null;
		if ($this->module->getConfigValue('MOLLIE_USE_PROFILE_WEBHOOK'))
		{
			unset($data['webhookUrl']);
		}

		try
		{
			/** @var Mollie_API_Object_Payment $payment */
			$payment = $this->module->api->payments->create($data);
		}
		catch (Mollie_API_Exception $e)
		{
			try
			{
				if ($e->getField() == "webhookUrl")
				{
					if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
					{
						Logger::addLog(__METHOD__ . ' said: Could not reach generated webhook url, falling back to profile webhook url.', Mollie::WARNING);
					}
					unset($data['webhookUrl']);
					$payment = $this->module->api->payments->create($data);
				}
				else
				{
					throw $e;
				}
			}
			catch (Mollie_API_Exception $e)
			{
				if ($this->module->getConfigValue('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
				{
					Logger::addLog(__METHOD__ . ' said: ' . $e->getMessage(), Mollie::CRASH);
				}
				if ($this->module->getConfigValue('MOLLIE_DISPLAY_ERRORS'))
				{
					die(
						$this->module->lang['There was an error while processing your request: '] .
						'<br /><i>' . $e->getMessage() . '</i><br /><br />' .
						'<a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . '">' .
						$this->module->lang['Click here to continue'] .
						'</a>.'
					);
				}
				else
				{
					Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__);
				}
			}
		}
		return $payment;
	}
}
