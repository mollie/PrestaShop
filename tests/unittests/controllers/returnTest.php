<?php
class returnTest extends PHPUnit_Framework_TestCase
{
	public $mollie;
	public $controller;

	public function setUp()
	{
		parent::setUp();
		$this->mollie = $this->getMock('Mollie', array(
			'lang',
			'getConfigValue',
			'reinstall',
		));
		$this->mollie->version = '1.0.0';
		$this->controller = $this->getMock('MollieReturnModuleFrontController', array(
			'setTemplate',
		));
		$this->controller->module = $this->mollie;
		$this->controller->context = new stdClass();
		$this->controller->context->smarty = $this->getMock('Smarty', array(
			'assign',
		));

		$this->mollie->expects($this->any())
			->method('lang')
			->will($this->returnArgument(0));
		$this->mollie->expects($this->any())
			->method('lang')
			->will($this->returnValue('0.0.0'));

		$_GET['id'] = 1;
	}

	public function testAuthorized()
	{
		// test if the page is visible if we have a correct access token
		$_GET['ref'] = 'UNIQREF';

		$this->controller->context->smarty->expects($this->once())
			->method('assign')
			->with(array(
				'auth' => TRUE,
				'mollie_info' => array('bank_status' => 'open'),
				'msg_continue' => '<a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . '">Continue shopping</a>',
				'msg_details' => 'We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.',
				'msg_welcome' => 'Welcome back',
			));

		// execute
		$this->controller->initContent();
	}

	public function testNotAuthorized()
	{
		// test if the page is denied if we have an incorrect access token
		$_GET['ref'] = 'WRONGREF';

		$this->controller->context->smarty->expects($this->once())
			->method('assign')
			->with(array(
				'auth' => FALSE,
				'mollie_info' => array(),
				'msg_continue' => '<a href="' . _PS_BASE_URL_ . __PS_BASE_URI__ . '">Continue shopping</a>',
				'msg_details' => 'You are not authorised to see this page.',
				'msg_welcome' => 'Welcome back',
			));

		// execute
		$this->controller->initContent();
	}
}