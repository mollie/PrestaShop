<?php
class webhookTest extends PHPUnit_Framework_TestCase
{
	public $mollie;
	public $controller;
	public $payment;

	public function setUp()
	{
		parent::setUp();
		/*
		$this->context = new stdClass();
		$this->context->cart = $this->getMock('Cart');
		*/
		$this->mollie = $this->getMock('Mollie', array(
			'getConfigValue',
			'setOrderStatus',
			'reinstall',
			'validateOrder'
		));
		$this->controller = new Mollie_Testing_Impostor($this->getMock('MollieWebhookModuleFrontController', array(
			'_savePaymentStatus',
			'_convertEuroToCartCurrency',
		)));
		$this->controller->module = $this->mollie;
		$this->controller->module->currentOrder = 666;
		$this->controller->module->active = TRUE;

		$this->controller->context = new stdClass();
		$this->controller->context->link = new Link();
		$this->controller->context->smarty = new Smarty();
		$this->controller->context->cart = new Cart();
		$this->controller->context->cart->id_currency = 1;

		$this->payment = $this->getMock('Mollie_API_Object_Payment');
		$this->payment->metadata = new stdClass();
		$this->payment->metadata->cart_id    = 777;
		$this->payment->metadata->secure_key = "-secure-key-";
		$this->mollie->api = $this->getMock('Mollie_API_Client');
		$this->mollie->api->payments = $this->getMock('Mollie_API_Resource_Payments', array(
			'get',
		), array(
			$this->mollie->api,
		));


		$this->mollie->api->payments->expects($this->any())
			->method('get')
			->will($this->returnValue($this->payment));

		$this->mollie->expects($this->any())
			->method('getConfigValue')
			->will($this->returnValue(FALSE));
	}

	public function testTellMollieOK()
	{
		// test if a request with testByMollie parameter results in OK
		$_GET['testByMollie'] = TRUE;
		$this->assertEquals('OK', $this->controller->_executeWebhook());
	}

	public function testNeedID()
	{
		// test if a request without id is denied
		unset($_GET['testByMollie']);
		$_GET['id'] = null;
		$this->assertEquals('NO ID', $this->controller->_executeWebhook());
	}

	public function testWebhookGivenCartId()
	{
		$this->payment->metadata->cart_id = 777;
		$this->payment->amount            = 15.95;

		// test if _saveOrderStatus is called correctly
		$_GET['id'] = 'tr_q2cLW9pxMT';
		$this->payment->status = 'paid';
		$this->payment->id = $_GET['id'];

		// somehow pass the validation
		$this->mollie->expects($this->once())
			->method('validateOrder');

		$this->controller->expects($this->once())
			->method('_savePaymentStatus')
			->with('tr_q2cLW9pxMT', 'paid');

		$this->controller->expects($this->once())
			->method('_convertEuroToCartCurrency')
			->with(15.95, 777);

		$this->assertEquals('OK', $this->controller->_executeWebhook());
	}

	public function testWebhookGivenOrderId()
	{
		$this->payment->metadata->order_id = 777;
		$this->payment->metadata->cart_id = NULL;

		// test if _saveOrderStatus is called correctly
		$_GET['id'] = 'tr_q2cLW9pxMT';
		$this->payment->status = 'paid';
		$this->payment->id = $_GET['id'];

		$this->controller->expects($this->once())
			->method('_savePaymentStatus')
			->with('tr_q2cLW9pxMT', 'paid');

		$this->mollie->expects($this->once())
			->method('setOrderStatus')
			->with(777, 'paid');

		$this->assertEquals('OK', $this->controller->_executeWebhook());
	}
}