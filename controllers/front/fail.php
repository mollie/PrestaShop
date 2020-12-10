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

use Mollie\Config\Config;
use PrestaShop\PrestaShop\Adapter\Order\OrderPresenter;

class MollieFailModuleFrontController extends ModuleFrontController
{
	/**
	 * ID Order Variable Declaration.
	 *
	 * @var int
	 */
	private $id_order;

	/**
	 * Security Key Variable Declaration.
	 *
	 * @var string
	 */
	private $secure_key;

	/**
	 * ID Cart Variable Declaration.
	 *
	 * @var int
	 */
	private $id_cart;

	/**
	 * Order Presenter Variable Declaration.
	 *
     * @phpstan-ignore-next-line
	 * @var OrderPresenter
	 */
	private $order_presenter;

	public function init()
	{
		if (!Config::isVersion17()) {
			return parent::init();
		}
		parent::init();

		$this->id_cart = (int) Tools::getValue('cartId', 0);

		$redirectLink = 'index.php?controller=history';

		$orderId = (int) Order::getOrderByCartId((int) $this->id_cart); /* @phpstan-ignore-line */

		$this->id_order = $orderId;
		$this->secure_key = Tools::getValue('secureKey');
		$order = new Order((int) $this->id_order);

		if (!$this->id_order || !$this->module->id || !$this->secure_key || empty($this->secure_key)) {
			Tools::redirect($redirectLink . (Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
		}

		if ((string) $this->secure_key !== (string) $order->secure_key ||
			(int) $order->id_customer !== (int) $this->context->customer->id ||
			!Validate::isLoadedObject($order)
		) {
			Tools::redirect($redirectLink);
		}

		if ($order->module !== $this->module->name) {
			Tools::redirect($redirectLink);
		}
		/* @phpstan-ignore-next-line */
		$this->order_presenter = new OrderPresenter();
	}

	public function initContent()
	{
		parent::initContent();

		$cartId = Tools::getValue('cartId');
		$moduleId = Tools::getValue('moduleId');
		$orderId = Tools::getValue('orderId');
		$secureKey = Tools::getValue('secureKey');

		$orderLink = $this->context->link->getPageLink(
			'order-confirmation',
			true,
			null,
			[
				'id_cart' => $cartId,
				'id_module' => $moduleId,
				'id_order' => $orderId,
				'key' => $secureKey,
				'cancel' => 1,
			]
		);
		if (!Config::isVersion17()) {
			Tools::redirect($orderLink);
		}

		$order = new Order($this->id_order);
		if ((bool) version_compare(_PS_VERSION_, '1.7', '>=')) {
			$this->context->smarty->assign([
				/* @phpstan-ignore-next-line */
				'order' => $this->order_presenter->present($order),
			]);
		} else {
			$this->context->smarty->assign([
				'id_order' => $this->id_order,
				'email' => $this->context->customer->email,
			]);
		}

		$this->setTemplate(
			sprintf('module:%s/views/templates/front/order_fail.tpl', $this->module->name)
		);
	}
}
