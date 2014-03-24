<?php
class webhookTest extends PHPUnit_Framework_TestCase
{
	public $mollie;
	public $controller;
	public $payment;

	public function setUp()
	{
		parent::setUp();
		$this->mollie = $this->getMock('Mollie', array(
			'getConfigValue',
			'setOrderStatus',
		));
		$this->controller = new Mollie_Testing_Impostor($this->getMock('MollieWebhookModuleFrontController', array(
			'_saveOrderStatus',
		)));
		$this->controller->module = $this->mollie;
		$this->payment = $this->getMock('Mollie_API_Object_Payment');
		$this->payment->metadata = new stdClass();
		$this->payment->metadata->order_id = 666;
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

	public function testEverythingGoesGreat()
	{
		// test if _saveOrderStatus is called correctly
		$_GET['id'] = 'tr_q2cLW9pxMT';
		$this->payment->status = 'paid';

		$this->controller->expects($this->once())
			->method('_saveOrderStatus')
			->with(666, 'paid');

		$this->mollie->expects($this->once())
			->method('setOrderStatus')
			->with(666, 'paid');

		$this->assertEquals('OK', $this->controller->_executeWebhook());
	}
}