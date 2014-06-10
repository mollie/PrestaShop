<?php
class paymentTest extends PHPUnit_Framework_TestCase
{
	public $mollie;
	public $controller;
	public $mollie_payment;

	public function setUp()
	{
		parent::setUp();
		$this->mollie = $this->getMock('Mollie', array(
			'getConfigValue',
			'validateOrder',
			'reinstall',
		));
		$this->controller = $this->getMock('MolliePaymentModuleFrontController', array(
			'_getIssuerList',
			'setTemplate',
			'_convertCurrencyToEuro',
			'_getPaymentData',
			'_createPayment',
		));
		$this->mollie_payment = $this->getMock('Mollie_API_Object_Payment', array(
			'getPaymentUrl'
		));
		$this->controller->module = $this->mollie;
		$this->controller->module->currentOrder = 666;
		$this->controller->module->active = TRUE;
		$this->controller->context = new stdClass();
		$this->controller->context->link = new Link();
		$this->controller->context->smarty = new Smarty();
		$this->controller->context->cart = new Cart();
		$this->controller->context->cart->id_customer = new Customer();
	}

	public function testShowModuleList()
	{
		// test if the module list is shown when applicable (config.MOLLIE_ISSUERS == Mollie::ISSUERS_OWN_PAGE)

		// make module list apply
		$this->mollie->expects($this->atLeastOnce())
			->method('getConfigValue')
			->will($this->returnValue(Mollie::ISSUERS_OWN_PAGE));

		// needs a method
		$_GET['method'] = 'ideal';

		// needs more than one issuer
		$this->controller->expects($this->once())
			->method('_getIssuerList')
			->will($this->returnValue(array('my_bank', 'your_bank')));

		// consider successful if it sets the template
		$this->controller->expects($this->once())
			->method('setTemplate')
			->with('mollie_issuers.tpl');

		// execute
		$this->controller->initContent();
	}

	public function testSuccessRedirect()
	{
		// test if the user is redirected if module list doesn't apply

		// make module list not apply
		$this->mollie->expects($this->atLeastOnce())
			->method('getConfigValue')
			->will($this->returnValue(Mollie::ISSUERS_PAYMENT_PAGE));

		// needs a method
		$_GET['method'] = 'ideal';

		// use mocked conversion (1:1 rate)
		$this->controller->expects($this->once())
			->method('_convertCurrencyToEuro')
			->will($this->returnValue(13.37));

		// somehow pass the validation
		$this->mollie->expects($this->once())
			->method('validateOrder');

		// set payment data
		$this->controller->expects($this->once())
			->method('_getPaymentData')
			->will($this->returnValue(array()));

		// create payment
		$this->controller->expects($this->once())
			->method('_createPayment')
			->will($this->returnValue($this->mollie_payment));

		// consider successful if getPaymentUrl gets called
		$this->mollie_payment->expects($this->once())
			->method('getPaymentUrl');

		// execute
		$this->controller->initContent();
	}

	public function testValidOrder()
	{
		// test if a valid order passes the validation
		$cart = new Cart();
		$customer = new Customer();
		$payments = new Mollie_Testing_Impostor($this->controller);
		$this->assertTrue($payments->_validate($cart, $customer));
	}

	public function testInvalidOrder()
	{
		// test if an invalid order gets denied by the validation
		$cart = new Cart();
		$cart->id_customer = null;
		$cart->id_address_delivery = null;
		$cart->id_address_invoice = null;
		$customer = new Customer();
		$payments = new Mollie_Testing_Impostor($this->controller);
		$this->assertFalse($payments->_validate($cart, $customer));
	}


}