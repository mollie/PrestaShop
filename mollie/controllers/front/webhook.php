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

class MollieWebhookModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$id = Tools::getValue('id');
		if (empty($id))
		{
			echo 'no id';
			exit;
		}

		// Store status in database

		/** @var Mollie_API_Object_Payment $payment */
		$payment  = $this->module->api->payments->get($id);
		$order_id = $payment->metadata->order_id;
		$status   = $payment->status;
		$details  = $payment->details;

		$data = array(
			'updated_at' => date("Y-m-d H:i:s"),
			'bank_status' => $status,
			'bank_account' => (isset($details->consumerAccount) ? $details->consumerAccount : '')
		);
		Db::getInstance()->update('mollie_payments', $data, '`order_id` = ' . (int) $order_id);

		// Tell status to Shop
		$status_id = (int) $this->module->statuses[$status];
		$history = new OrderHistory();
		$history->id_order = $order_id;
		$history->id_order_state = $status_id;
		$history->changeIdOrderState($status_id, $order_id);
		// Possibly also notify customer
		if (Configuration::get('MOLLIE_MAIL_WHEN_' . strtoupper($status)))
		{
			$history->addWithemail();
		}
		else
		{
			$history->add();
		}
	}
}