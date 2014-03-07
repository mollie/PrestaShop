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

class MolliePaymentModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		try {
		parent::initContent();

		$cart = $this->context->cart;
		$customer = new Customer($cart->id_customer);

		if (!$this->_validate($cart, $customer))
		{
			die($this->module->l('This payment method is not available.', 'validation'));
		}

		$method = $_GET['method'];
		$issuer = !empty($_GET['issuer']) ? $_GET['issuer'] : null;

		$total = $cart->getOrderTotal(true, Cart::BOTH);
		$currency_euro = Currency::getIdByIsoCode('EUR');
		if (!$currency_euro) {
			// No Euro currency available!
			die($this->module->l('This payment method is only available for Euros.'));
		}

		if ($cart->id_currency !== $currency_euro)
		{
			// convert non-euro currency to default
			$total = Tools::convertPrice($total, $cart->id_currency, FALSE);
			if (Currency::getDefaultCurrency() !== $currency_euro)
			{
				// if default is not euro, convert to euro
				$total = Tools::convertPrice($total, $currency_euro, TRUE);
			}
			$total = round($total, 2);
		}

		$this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PREPARATION'), $total, $method, NULL, array(), null, false, $customer->secure_key);

		$order_id = $this->module->currentOrder;
		$link = new Link();

		$payment_data = array(
			"amount"       => $total,
			"method"       => $method,
			"issuer"       => $issuer,
			"description"  => str_replace('%', $order_id, Configuration::get('MOLLIE_DESCRIPTION')),
			"redirectUrl"  => $link->getModuleLink('mollie', 'return', array('id' => $order_id, 'ref' => Order::getUniqReferenceOf($order_id))),
			"metadata"     => array(
				"order_id" => $order_id,
			),
		);

		if (isset($this->context, $this->context->cart))
		{
			if (isset($this->context->cart->id_address_invoice))
			{
				$billing	= new Address(intval($this->context->cart->id_address_invoice));
				$payment_data['billingCity'] = $billing->city;
				$payment_data['billingRegion'] = State::getNameById($billing->id_state);
				$payment_data['billingPostal'] = $billing->postcode;
				$payment_data['billingCountry'] = Country::getIsoById($billing->id_country);
			}
			if (isset($this->context->cart->id_address_delivery))
			{
				$shipping	= new Address(intval($this->context->cart->id_address_delivery));
				$payment_data['shippingCity'] = $shipping->city;
				$payment_data['shippingRegion'] = State::getNameById($shipping->id_state);
				$payment_data['shippingPostal'] = $shipping->postcode;
				$payment_data['shippingCountry'] = Country::getIsoById($shipping->id_country);
			}
		}

		/** @var Mollie_API_Object_Payment $payment */
		$payment = $this->module->api->payments->create($payment_data);

		Db::getInstance()->insert('mollie_payments', array(
			'order_id' => $order_id,
			'method' => $payment->method,
			'transaction_id' => $payment->id,
			'bank_status' => Mollie_API_Object_Payment::STATUS_OPEN,
			'created_at' => date("Y-m-d H:i:s")
		));

		Tools::redirect($payment->getPaymentUrl());
		}catch(Exception $e){die($e);}
	}


	/**
	 * Checks if this payment option is still available
	 * May redirect the user to a more appropriate page
	 * @param $cart
	 * @return bool
	 */
	public function _validate($cart, $customer)
	{
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
		{
			Tools::redirect('index.php');
			return false;
		}
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
		{
			if ($module['name'] == 'mollie')
			{
				$authorized = true;
				break;
			}
		}
		if (!$authorized)
		{
			return false;
		}
		if (!Validate::isLoadedObject($customer))
		{
			Tools::redirect('index.php?controller=order&step=1');
			return false;
		}
		return true;
	}
}
