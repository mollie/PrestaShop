<?php

date_default_timezone_set("CET");
error_reporting(-1);
define('_PS_VERSION_', 1);
define('_DB_PREFIX_', '_');
define('_PS_BASE_URL_', 'http://prestashop.dev/');
define('__PS_BASE_URI__', 'index.php');
define('_PS_THEME_DIR_', '/');
define('_PS_CSS_DIR_', '/');
define('_THEME_MOBILE_DIR_', '/');
define('_THEME_MOBILE_CSS_DIR_', '/');
define('_PS_THEME_OVERRIDE_DIR_', '/');


$base = dirname(dirname(__FILE__)) . '/mollie/';
require_once($base . '../tests/impostor.php');
require_once($base . 'mollie.php');
require_once($base . 'controllers/front/payment.php');
require_once($base . 'controllers/front/return.php');
require_once($base . 'controllers/front/webhook.php');

class Mollie_Testing_Exception extends Exception {}

class ModuleFrontController
{
	public $module;
	public $_path;
	public $context;
	function initContent() {}
}

class Module
{
	static function getPaymentModules() { return array(array('name' => 'mollie')); }
}

class PaymentModule
{
	function __construct() {}
	function install() { return TRUE; }
	function uninstall() { return TRUE; }
	function l($txt, $mod = '') { return $txt; }
}

class Configuration
{
	static function get() { return FALSE; }
	static function updateValue() { return TRUE; }
	static function deleteByName() { return TRUE; }
}

class Validate
{
	static function isLoadedObject() { return TRUE; }
}

class Logger
{
	static function addLog() {}
}

class Currency
{
	static function exists() { return TRUE; }
	static function getIdByIsoCode() { return 1; }
	static function getDefaultCurrency() { return 1; }
}

class Tools
{
	static function isSubmit($field) { return isset($_POST[$field]); }
	static function htmlentitiesUTF8() { return ''; }
	static function redirect() { }
	static function convertPrice() { return 0; }
	static function getValue($key, $default = FALSE)
	{
		if (isset($_POST[$key]))
		{
			return $_POST[$key];
		}
		elseif (isset($_GET[$key]))
		{
			return $_GET[$key];
		}
		else
		{
			return $default;
		}
	}
}

class Link
{
	public function getAdminLink() { return ''; }
}

class Db
{
	static function getInstance() { return new Db(); }
	function getValue() { return FALSE; }
	function getRow() { return array('bank_status' => 'open'); }
	function execute() { return TRUE; }
	function insert() { return TRUE; }
	function update() { return TRUE; }
	function getMsgError() { return ''; }
	function escape($a) { return $a; }
}

class Smarty
{
	public function assign() { }
}

class Order
{
	static function getUniqReferenceOf() { return 'UNIQREF'; }
}

class OrderState
{
	static function getOrderStates() { return array(); }
}

class OrderHistory
{
	function changeIdOrderState() {}
	function addWithemail() {}
	function add() {}
}

class Cart
{
	const BOTH = 'BOTH';
	public $id = null;
	public $id_customer = 666;
	public $id_address_delivery = 1;
	public $id_address_invoice = 1;
	function __construct()
	{
		$this->id_address_delivery = new Address();
		$this->id_address_invoice = new Address();
	}
	function getOrderTotal() { return 13.37; }
}

class Customer
{
	public $secure_key = '';
}

class Address
{
	public $city = '';
	public $id_state = '';
	public $id_country = '';
	public $postcode = '';
}

class State
{
	static function getNameById() { return ''; }
}

class Country
{
	static function getIsoById() { return ''; }
}
