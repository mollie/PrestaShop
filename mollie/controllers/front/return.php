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

class MollieReturnModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		ini_set('display_errors', true);
		parent::initContent();

		$order_id = (int) $_GET['id'];

		// Check if user is allowed to be on the return page
		$data['auth'] = Order::getUniqReferenceOf($order_id) === $_GET['ref'];

		// Get order information (if user is allowed to see it)
		if ($data['auth'])
		{
			$data['mollie_info'] = Db::getInstance()->getRow(sprintf(
				'SELECT * FROM `%s` WHERE `order_id` = %d',
				_DB_PREFIX_ . 'mollie_payments',
				$order_id
			));

			switch ($data['mollie_info']['bank_status'])
			{
				case Mollie_API_Object_Payment::STATUS_OPEN:
					$data['msg_details'] = $this->l('We have not received a definite payment status. You will receive an email as soon as we receive a confirmation of the bank/merchant.', 'mollie');
					break;
				case Mollie_API_Object_Payment::STATUS_CANCELLED:
					$data['msg_details'] = $this->l('You have cancelled your order.', 'mollie');
					break;
				case Mollie_API_Object_Payment::STATUS_EXPIRED:
					$data['msg_details'] = $this->l('Unfortunately your order was expired.', 'mollie');
					break;
				case Mollie_API_Object_Payment::STATUS_PAID:
					$data['msg_details'] = $this->l('Thank you. Your order has been received.', 'mollie');
					break;
			}
		}
		// Not allowed? Don't make query but redirect.
		else
		{
			$data['mollie_info'] = array();
			$data['msg_details'] = $this->l('You are not authorised to see this page.', 'mollie');
			Tools::redirect('index.php');
		}

		$data['msg_continue'] = '<a href="index.php">' . $this->l('Continue shopping', 'mollie') . '</a>';

		$this->context->smarty->assign($data);
		$this->setTemplate('mollie_return.tpl');
	}

	public function l($str, $module = 'mollie')
	{
		return $this->module->l($str, $module);
	}
}