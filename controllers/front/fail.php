<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
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
	 *
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
