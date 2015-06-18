<?php
class mollieTest extends PHPUnit_Framework_TestCase
{
	public $mollie;

	public function setUp()
	{
		parent::setUp();
		$this->mollie = $this->getMock('Mollie', array(
			'registerHook',
			'unregisterHook',
			'initConfigValue',
			'deleteConfigValue',
			'updateConfigValue',
			'display',
			'_getUpdateXML',
		));

		$this->mollie->version = '1.0.0';
		$this->mollie->context = new stdClass();
		$this->mollie->context->link = new Link();
		$this->mollie->context->smarty = new Smarty();

		$_SERVER['REQUEST_URI'] = 'something.dev';
	}

	public function testInstall()
	{
		// test if registerHook is called
		$this->mollie->expects($this->atLeastOnce())
			->method('registerHook')
			->will($this->returnValue(TRUE));

		// test if initConfigValue is called
		$this->mollie->expects($this->atLeastOnce())
			->method('initConfigValue')
			->will($this->returnValue(TRUE));

		// execute
		$this->mollie->install();
	}

	public function testUninstall()
	{
		// test if unregisterHook is called
		$this->mollie->expects($this->atLeastOnce())
			->method('unregisterHook')
			->will($this->returnValue(TRUE));

		// test if deleteConfigValue is called
		$this->mollie->expects($this->atLeastOnce())
			->method('deleteConfigValue')
			->will($this->returnValue(TRUE));

		// execute
		$this->mollie->uninstall();
	}

	public function testSaveConfig()
	{
		$this->setUpdateXML('0.0.0');

		// create post array
		$_POST = array(
			'Mollie_Config_Save'          => TRUE,
			'Mollie_Api_Key'              => 'test_something',
			'Mollie_Description'          => 'Order %',
			'Mollie_Paymentscreen_Locale' => Mollie::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE,
			'Mollie_Images'               => Mollie::LOGOS_NORMAL,
			'Mollie_Issuers'              => Mollie::ISSUERS_ON_CLICK,
			'Mollie_Logger'               => 1,
			'Mollie_Errors'               => FALSE,
			'Mollie_Status_open'          => 3,
			'Mollie_Status_paid'          => 2,
			'Mollie_Status_cancelled'     => 6,
			'Mollie_Status_expired'       => 8,
			'Mollie_Status_refunded'      => 7,
		);

		// create valueMap to check if post values are passed to updateConfigValue
		$valueMap = array();
		foreach ($_POST as $name => $val)
		{
			$valueMap[] = array($name, $val);
		}

		// test if updateConfigValue is called right
		$this->mollie->expects($this->atLeastOnce())
			->method('updateConfigValue')
			->will($this->returnValueMap($valueMap));

		// execute
		$this->mollie->getContent();
	}

	public function testUpdateMessage()
	{
		// give _getUpdateMessage some githubish xml
		$this->setUpdateXML('999.0.0');

		// make mollie impostor to test protected _getUpdateMessage
		$mollie = new Mollie_Testing_Impostor($this->mollie);

		// execute
		$this->assertEquals(
			'<a href="https://github.com/mollie/Prestashop/releases">You are currently using version 1.0.0. We strongly recommend you to upgrade to the new version 999.0.0!</a>',
			$mollie->_getUpdateMessage('https://github.com/mollie/Prestashop')
		);
	}


	private function setUpdateXML($version = '0.0.0')
	{
		$this->mollie->expects($this->any())
			->method('_getUpdateXML')
			->with('https://github.com/mollie/Prestashop')
			->will($this->returnValue('<?xml version="1.0" encoding="UTF-8"?>
				<feed>
				  <entry>
					<id>tag:github.com,1970:Repository/123456789/' . $version . '</id>
				  </entry>
				</feed>
  			'));
	}
}