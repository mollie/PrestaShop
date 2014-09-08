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

class MollieReturnModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$data = array();
		/**
		 * Set ref is indicative of a payment that is tied to an order instead of a cart, which
		 * we still support for transitional reasons.
		 */
		if (isset($_GET['ref']))
		{
			$order_id = (int) $_GET['id'];

			// Check if user is allowed to be on the return page
			$data['auth'] = Order::getUniqReferenceOf($order_id) === $_GET['ref'];
			if ($data['auth'])
			{
				$data['mollie_info'] = $this->module->getPaymentBy('order_id', (int)$order_id);
			}
		}
		elseif (isset($_GET['cart_id']))
		{
			$cart_id = (int) $_GET['cart_id'];

			// Check if user that's seeing this is the cart-owner
			$cart = new Cart($cart_id);
			$data['auth'] = (int)$cart->id_customer === $this->context->customer->id;
			if ($data['auth'])
			{
				$data['mollie_info'] = $this->module->getPaymentBy('cart_id', (int)$cart_id);
			}
		}

		if (isset($data['auth']) && $data['auth'])
		{
			// any paid payments for this cart?

			if ($data['mollie_info'] === FALSE)
			{
				$data['mollie_info'] = array();
				$data['msg_details'] = $this->module->lang('The order with this id does not exist.');
			}
			else
			{
				switch ($data['mollie_info']['bank_status'])
				{
					case Mollie_API_Object_Payment::STATUS_OPEN:
						$data['msg_details'] = $this->module->lang('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.');
						break;
					case Mollie_API_Object_Payment::STATUS_CANCELLED:
						Tools::redirect('/index.php?controller=order&step=3');
						break;
					case Mollie_API_Object_Payment::STATUS_EXPIRED:
						$data['msg_details'] = $this->module->lang('Unfortunately your order was expired.');
						break;
					case Mollie_API_Object_Payment::STATUS_PAID:
						if(isset($cart_id))
						{
							Tools::redirectLink(__PS_BASE_URI__ . 'index.php?controller=order-confirmation&id_cart=' . $cart_id .'&id_module='. $this->module->id .'&id_order=' . Order::getOrderByCartId(intval($cart_id)) . '&key=' . $this->context->customer->secure_key);
						}
						$data['msg_details'] = $this->module->lang('Thank you. Your order has been received.');
						break;
					default:
						$data['msg_details'] = $this->module->lang('The transaction has an unexpected status.');
						if (Configuration::get('MOLLIE_DEBUG_LOG') == Mollie::DEBUG_LOG_ERRORS)
						{
							Logger::addLog(__METHOD__ . 'said: The transaction has an unexpected status ('.$data['mollie_info']['bank_status'].')', Mollie::WARNING);
						}
				}
			}
		}
		// Not allowed? Don't make query but redirect.
		else
		{
			$data['mollie_info'] = array();
			$data['msg_details'] = $this->module->lang('You are not authorised to see this page.');
			Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__);
		}

		$data['msg_continue'] = '<a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . '">' . $this->module->lang('Continue shopping') . '</a>';
		$data['msg_welcome'] = $this->module->lang('Welcome back');

		$this->context->smarty->assign($data);
		$this->setTemplate('mollie_return.tpl');
	}
}
