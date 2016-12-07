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

/**
 * Class Mollie
 * @method l
 * @method display
 * @method registerHook
 * @method unregisterHook
 * @method validateOrder
 * @property mixed warning
 * @property mixed _errors
 * @property mixed _path
 * @property mixed smarty
 * @property mixed context
 * @property mixed currentOrder
 * @property mixed active
 */

class Mollie extends PaymentModule
{
	/** @var Mollie_API_Client|null */
	public $api                    = NULL;
	public $lang                   = array();
	public $statuses               = array();

	const NOTICE  = 1;
	const WARNING = 2;
	const ERROR   = 3;
	const CRASH   = 4;

	const NAME = 'mollie';

	const PAYMENTSCREEN_LOCALE_BROWSER_LOCALE      = 'browser_locale';
	const PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE = 'website_locale';

	const LOGOS_BIG    = 'big';
	const LOGOS_NORMAL = 'normal';
	const LOGOS_HIDE   = 'hide';

	const ISSUERS_ALWAYS_VISIBLE = 'always-visible';
	const ISSUERS_ON_CLICK       = 'on-click';
	const ISSUERS_OWN_PAGE       = 'own-page';
	const ISSUERS_PAYMENT_PAGE   = 'payment-page';

	const DEBUG_LOG_NONE   = 0;
	const DEBUG_LOG_ERRORS = 1;
	const DEBUG_LOG_ALL    = 2;

	public function __construct()
	{
		$this->name                   = 'mollie';
		$this->author                 = 'Mollie B.V.';
		$this->tab                    = 'payments_gateways';
		$this->version                = '1.3.0';
		$this->need_instance          = true;

		parent::__construct();

		$this->displayName = $this->l('Mollie Payment Module');
		$this->description = $this->l('Mollie Payments');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall the Mollie Payment Module?');

		$this->controllers = array('payment', 'return', 'webhook');

		require_once(dirname(__FILE__) . '/lib/src/Mollie/API/Autoloader.php');

		try
		{
			$this->api = new Mollie_API_Client;
			$this->api->setApiKey($this->getConfigValue('MOLLIE_API_KEY'));
			$this->api->addVersionString('Prestashop/' . (defined('_PS_VERSION_') ? _PS_VERSION_ : 'Unknown'));
			$this->api->addVersionString('MolliePrestashop/' . (isset($this->version) ? $this->version : 'Unknown'));
		}
		catch (Mollie_API_Exception_IncompatiblePlatform $e)
		{
			Logger::addLog(__METHOD__ . ' - System incompatible: ' . $e->getMessage(), Mollie::CRASH);
		}
		catch (Mollie_API_Exception $e)
		{
			$this->warning = $this->l('Payment error:') . $e->getMessage();
			Logger::addLog(__METHOD__ . ' said: ' . $this->warning, Mollie::CRASH);
		}

		$this->statuses = array(
			Mollie_API_Object_Payment::STATUS_PAID      => $this->getConfigValue('MOLLIE_STATUS_PAID'),
			Mollie_API_Object_Payment::STATUS_CANCELLED => $this->getConfigValue('MOLLIE_STATUS_CANCELLED'),
			Mollie_API_Object_Payment::STATUS_EXPIRED   => $this->getConfigValue('MOLLIE_STATUS_EXPIRED'),
			Mollie_API_Object_Payment::STATUS_REFUNDED  => $this->getConfigValue('MOLLIE_STATUS_REFUNDED'),
			Mollie_API_Object_Payment::STATUS_OPEN      => $this->getConfigValue('MOLLIE_STATUS_OPEN'),
		);

		// Load all translatable text here so we have a single translation point
		$this->lang = array(
			Mollie_API_Object_Payment::STATUS_PAID               => $this->l('paid'),
			Mollie_API_Object_Payment::STATUS_CANCELLED          => $this->l('cancelled'),
			Mollie_API_Object_Payment::STATUS_EXPIRED            => $this->l('expired'),
			Mollie_API_Object_Payment::STATUS_REFUNDED           => $this->l('refunded'),
			Mollie_API_Object_Payment::STATUS_OPEN               => $this->l('bankwire pending'),
			'This payment method is not available.'              => $this->l('This payment method is not available.'),
			'Click here to continue'                             => $this->l('Click here to continue'),
			'This payment method is only available for Euros.'   => $this->l('This payment method is only available for Euros.'),
			'There was an error while processing your request: ' => $this->l('There was an error while processing your request: '),
			'The order with this id does not exist.'             => $this->l('The order with this id does not exist.'),
			'We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.' =>
				$this->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.'),
			'You have cancelled your payment.'                     => $this->l('You have cancelled your payment.'),
			'Unfortunately your payment was expired.'              => $this->l('Unfortunately your payment was expired.'),
			'Thank you. Your payment has been received.'           => $this->l('Thank you. Your payment has been received.'),
			'The transaction has an unexpected status.'          => $this->l('The transaction has an unexpected status.'),
			'You are not authorised to see this page.'           => $this->l('You are not authorised to see this page.'),
			'Continue shopping'                                  => $this->l('Continue shopping'),
			'Welcome back'                                       => $this->l('Welcome back'),
			'Select your bank:'                                  => $this->l('Select your bank:'),
			'OK'                                                 => $this->l('OK'),
			'Return to the homepage'                             => $this->l('Return to the homepage'),
			'Pay with %s'                                        => $this->l('Pay with %s'),
			'Refund this order'                                  => $this->l('Refund this order'),
			'Mollie refund'                                      => $this->l('Mollie refund'),
			'Refund order #%d through the Mollie API.'           => $this->l('Refund order #%d through the Mollie API.'),
			'iDEAL'                                              => $this->l('iDEAL'),
			'Creditcard'                                         => $this->l('Creditcard'),
			'Bancontact/Mister Cash'                             => $this->l('Bancontact/Mister Cash'),
			'SOFORT Banking'                                     => $this->l('SOFORT Banking'),
			'SEPA Direct Debit'                                  => $this->l('SEPA Direct Debit'),
			'Belfius Direct Net'                                 => $this->l('Belfius Direct Net'),
			'Bitcoin'                                        	 => $this->l('Bitcoin'),
			'PODIUM Cadeaukaart'                                 => $this->l('PODIUM Cadeaukaart'),
			'Bank transfer'                                      => $this->l('Bank transfer'),
			'PayPal'                                             => $this->l('PayPal'),
			'paysafecard'                                        => $this->l('paysafecard'),
			'MiniTix'                                            => $this->l('MiniTix'),
			'Micropayments'                                      => $this->l('Micropayments'),
		);

		// If an update includes a new hook, it normally takes a manual reinstall for it to take effect
		// This would cause all config values to reset and the Mollie table to be cleared.
		// $this->reinstall() fixes the hook registration without those sad side effects.
		$version = $this->getConfigValue('MOLLIE_VERSION');
		if ($version === FALSE || version_compare($version, $this->version, '<'))
		{
			$this->reinstall();
			$this->updateConfigValue('MOLLIE_VERSION', $this->version);
		}
	}


	/**
	 * Installs the Mollie Payments Module
	 *
	 * @return bool
	 */
	public function install()
	{
		if (
			!parent::install() ||
			!$this->_registerHooks()
		)
		{
			$this->_errors[] = 'Unable to install module';
			return FALSE;
		}
		if (
		!$this->_initConfig()
		)
		{
			$this->_errors[] = 'Unable to set config values';
			return FALSE;
		}

		$sql = sprintf('
			CREATE TABLE IF NOT EXISTS `%s` (
				`transaction_id` VARCHAR(64) NOT NULL PRIMARY KEY,
				`cart_id` INT(64),
				`order_id` INT(64),
				`method` VARCHAR(128) NOT NULL,
				`bank_status` VARCHAR(64) NOT NULL,
				`created_at` DATETIME NOT NULL,
				`updated_at` DATETIME DEFAULT NULL,
				 INDEX (cart_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
			_DB_PREFIX_ . 'mollie_payments'
		);

		if (!Db::getInstance()->execute($sql))
		{
			$this->_errors[] = 'Database error: ' . Db::getInstance()->getMsgError();
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return bool
	 */
	public function uninstall()
	{
		if (!$this->_unregisterHooks())
		{
			$this->_errors[] = 'Unable to unregister the module';
			return FALSE;
		}
		if (
			!$this->deleteConfigValue('MOLLIE_VERSION') ||
			!$this->deleteConfigValue('MOLLIE_API_KEY') ||
			!$this->deleteConfigValue('MOLLIE_DESCRIPTION') ||
			!$this->deleteConfigValue('MOLLIE_PAYMENTSCREEN_LOCALE') ||
			!$this->deleteConfigValue('MOLLIE_IMAGES') ||
			!$this->deleteConfigValue('MOLLIE_ISSUERS') ||
			!$this->deleteConfigValue('MOLLIE_CSS') ||
			!$this->deleteConfigValue('MOLLIE_DEBUG_LOG') ||
			!$this->deleteConfigValue('MOLLIE_DISPLAY_ERRORS') ||
			!$this->deleteConfigValue('MOLLIE_USE_PROFILE_WEBHOOK') ||
			!$this->deleteConfigValue('MOLLIE_STATUS_OPEN') ||
			!$this->deleteConfigValue('MOLLIE_STATUS_PAID') ||
			!$this->deleteConfigValue('MOLLIE_STATUS_CANCELLED') ||
			!$this->deleteConfigValue('MOLLIE_STATUS_EXPIRED') ||
			!$this->deleteConfigValue('MOLLIE_STATUS_REFUNDED') ||
			!$this->deleteConfigValue('MOLLIE_MAIL_WHEN_OPEN') ||
			!$this->deleteConfigValue('MOLLIE_MAIL_WHEN_PAID') ||
			!$this->deleteConfigValue('MOLLIE_MAIL_WHEN_CANCELLED') ||
			!$this->deleteConfigValue('MOLLIE_MAIL_WHEN_EXPIRED') ||
			!$this->deleteConfigValue('MOLLIE_MAIL_WHEN_REFUNDED')
		)
		{
			$this->_errors[] = 'Unable to unset the configuration';
			return FALSE;
		}

		$sql = sprintf('DROP TABLE IF EXISTS `%s`;',
			_DB_PREFIX_ . 'mollie_payments'
		);

		if (!Db::getInstance()->execute($sql))
		{
			$this->_errors[] = 'Database error: ' . Db::getInstance()->getMsgError();
			return FALSE;
		}
		return parent::uninstall();
	}


	public function reinstall()
	{
		return
			$this->reinstallHooks() &&
			$this->_initConfig()
			;
	}

	public function reinstallHooks()
	{
		return
			$this->_unregisterHooks() &&
			$this->_registerHooks()
			;
	}

	/**
	 * @return bool
	 */
	protected function _registerHooks()
	{
		return
			$this->_registerHook('displayPayment') &&
			$this->_registerHook('displayPaymentEU', false) &&   // not standard presta hook
			$this->_registerHook('displayPaymentTop') &&
			$this->_registerHook('displayAdminOrder') &&
			$this->_registerHook('displayHeader') &&
			$this->_registerHook('displayBackOfficeHeader') &&
			$this->_registerHook('displayOrderConfirmation')
			;
	}

	/**
	 * @return bool
	 */
	protected function _unregisterHooks()
	{
		return
			$this->_unregisterHook('displayPayment') &&
			$this->_unregisterHook('displayPaymentEU', false) &&   // not standard presta hook
			$this->_unregisterHook('displayPaymentTop') &&
			$this->_unregisterHook('displayAdminOrder') &&
			$this->_unregisterHook('displayHeader') &&
			$this->_unregisterHook('displayBackOfficeHeader') &&
			$this->_unregisterHook('displayOrderConfirmation')
			;
	}

	/**
	 * Override Registering, check hook existence first.
	 * On false return register completion, so installation doesn't fail if the Hook doesn't exist.
	 * For example the PaymentEU hook doesn't exist in every prestashop, and can therefore produce an unstallation error.
	 * @param string $name Name of the hook to register
	 * @param bool $standard If the hook is standard prestashop, therefore we can assume the hook exists
	 * @return bool
	 */
	protected function _registerHook($name, $standard = true)
	{
		if (!$standard && !Hook::getIdByName($name))
		{
			return true;
		}

		return $this->registerHook($name);
	}

	/**
	 * Override Unregistering, check hook existence first.
	 * On false return unregister completion, so uninstallation doesn't fail if the Hook doesn't exist.
	 * For example the PaymentEU hook doesn't exist in every prestashop, and can therefore produce a uninstallation error.
	 * @param string $name Name of the hook to unregister
	 * @param bool $standard If the hook is standard prestashop, therefore we can assume the hook exists
	 * @return bool
	 */
	protected function _unregisterHook($name, $standard = true)
	{
		if (!$standard && !Hook::getIdByName($name))
		{
			return true;
		}

		return $this->unregisterHook($name);
	}

	/**
	 * @return bool
	 */
	protected function _initConfig()
	{
		return
			$this->initConfigValue('MOLLIE_VERSION', $this->version) &&
			$this->initConfigValue('MOLLIE_API_KEY', '') &&
			$this->initConfigValue('MOLLIE_DESCRIPTION', 'Cart %') &&
			$this->initConfigValue('MOLLIE_PAYMENTSCREEN_LOCALE', self::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE) &&
			$this->initConfigValue('MOLLIE_IMAGES', self::LOGOS_NORMAL) &&
			$this->initConfigValue('MOLLIE_ISSUERS', self::ISSUERS_ON_CLICK) &&
			$this->initConfigValue('MOLLIE_CSS', '') &&
			$this->initConfigValue('MOLLIE_DEBUG_LOG', self::DEBUG_LOG_ERRORS) &&
			$this->initConfigValue('MOLLIE_DISPLAY_ERRORS', FALSE) &&
			$this->initConfigValue('MOLLIE_USE_PROFILE_WEBHOOK', FALSE) &&
			$this->initConfigValue('MOLLIE_STATUS_OPEN', 10) &&
			$this->initConfigValue('MOLLIE_STATUS_PAID', 2) &&
			$this->initConfigValue('MOLLIE_STATUS_CANCELLED', 6) &&
			$this->initConfigValue('MOLLIE_STATUS_EXPIRED', 8) &&
			$this->initConfigValue('MOLLIE_STATUS_REFUNDED', 7) &&
			$this->initConfigValue('MOLLIE_MAIL_WHEN_PAID', TRUE) &&
			$this->initConfigValue('MOLLIE_MAIL_WHEN_CANCELLED', FALSE) &&
			$this->initConfigValue('MOLLIE_MAIL_WHEN_EXPIRED', FALSE) &&
			$this->initConfigValue('MOLLIE_MAIL_WHEN_REFUNDED', TRUE)
			;
	}

	/**
	 * @return mixed
	 */
	public function getErrors()
	{
		return $this->_errors;
	}


	/**
	 * @return string
	 */
	public function getContent()
	{
		$cookie = Context::getContext()->cookie;
		$lang = isset($cookie->id_lang) ? (int) $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT');
		$lang = $lang == 0 ? Configuration::get('PS_LANG_DEFAULT') : $lang;

		$update_message = $this->_getUpdateMessage('https://github.com/mollie/Prestashop');
		$result_msg     = '';

		$payscreen_locale_options = array(
			self::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE      => $this->l('Do not send locale, use browser language'),
			self::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE => $this->l('Send webshop locale'),
		);

		$image_options = array(
			self::LOGOS_BIG              => $this->l('big'),
			self::LOGOS_NORMAL           => $this->l('normal'),
			self::LOGOS_HIDE             => $this->l('hide')
		);
		$issuer_options = array(
			self::ISSUERS_ALWAYS_VISIBLE => $this->l('Always visible'),
			self::ISSUERS_ON_CLICK       => $this->l('On click'),
			self::ISSUERS_OWN_PAGE       => $this->l('Own page'),
			self::ISSUERS_PAYMENT_PAGE   => $this->l('Payment page')
		);
		$logger_options = array(
			self::DEBUG_LOG_NONE         => $this->l('Nothing'),
			self::DEBUG_LOG_ERRORS       => $this->l('Errors'),
			self::DEBUG_LOG_ALL          => $this->l('Everything')
		);

		if (Tools::isSubmit('Mollie_Config_Save'))
		{
			$result_msg = $this->_getSaveResult(array_keys($payscreen_locale_options), array_keys($image_options), array_keys($issuer_options), array_keys($logger_options));
		}

		$data = array(
			'form_action'              => Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
			'config_title'             => $this->l('Mollie Configuration'),
			'config_legend'            => $this->l('Mollie Settings'),
			'update_message'           => $update_message,
			'all_statuses'             => OrderState::getOrderStates($lang),
			'payscreen_locale_options' => $payscreen_locale_options,
			'image_options'            => $image_options,
			'issuer_options'           => $issuer_options,
			'logger_options'           => $logger_options,
			'title_status'             => $this->l('%s statuses:'),
			'title_visual'             => $this->l('Visual settings:'),
			'title_debug'              => $this->l('Debug info:'),
			'msg_result'               => $result_msg,
			'msg_api_key'              => $this->l('API key:'),
			'msg_desc'                 => $this->l('Description:'),
			'msg_payscreen_locale'     => $this->l('Send locale for payment screen'),
			'msg_images'               => $this->l('Images:'),
			'msg_issuers'              => $this->l('Issuer list:'),
			'msg_css'                  => $this->l('Css file:'),
			'msg_errors'               => $this->l('Display errors:'),
			'msg_logger'               => $this->l('Log level:'),
			'msg_save'                 => $this->l('Save settings:'),
			'desc_api_key'             => sprintf(
				$this->l('You can find your API key in your %sMollie Profile%s; it starts with test or live.'),
				'<a href="https://www.mollie.nl/beheer/account/profielen/" target="_blank">',
				'</a>'
			),
			'desc_desc'                => $this->l('Enter a description here. Note: Payment methods may have a character limit, best keep the description under 29 characters.'),
			'desc_payscreen_locale'    => sprintf(
				$this->l('Should the plugin send the current webshop %slocale%s to Mollie. Mollie payment screens will be in the same language as your webshop. Mollie can also detect the language based on the user\'s browser language.'),
				'<a href="https://en.wikipedia.org/wiki/Locale" target="_blank">',
				'</a>'
			),
			'desc_images'              => $this->l('Show big, normal or no payment method logos on checkout.'),
			'desc_issuers'             => $this->l('Some payment methods (eg. iDEAL) have an issuer list. This setting specifies where it is shown.'),
			'desc_css'                 => $this->l('Leave empty for default stylesheet. Should include file path when set.')
										. '<br />'
										. $this->l('Hint: You can use {BASE}, {THEME}, {CSS}, {MOBILE}, {MOBILE_CSS} and {OVERRIDE} for easy folder mapping.'),
			'desc_errors'              => $this->l('Enabling this feature will display error messages (if any) on the front page. Use for debug purposes only!'),
			'desc_logger'              => sprintf(
				$this->l('Recommended level: Errors. Set to Everything to monitor incoming webhook requests. %sView logs%s'),
				'<a href=' . $this->context->link->getAdminLink('AdminLogs') . '>',
				'</a>'
			),
			'val_api_key'              => $this->getConfigValue('MOLLIE_API_KEY'),
			'val_desc'                 => $this->getConfigValue('MOLLIE_DESCRIPTION'),
			'payscreen_locale_value'   => $this->getConfigValue('MOLLIE_PAYMENTSCREEN_LOCALE'),
			'val_images'               => $this->getConfigValue('MOLLIE_IMAGES'),
			'val_issuers'              => $this->getConfigValue('MOLLIE_ISSUERS'),
			'val_css'                  => $this->getConfigValue('MOLLIE_CSS'),
			'val_errors'               => $this->getConfigValue('MOLLIE_DISPLAY_ERRORS'),
			'val_logger'               => $this->getConfigValue('MOLLIE_DEBUG_LOG'),
			'val_save'                 => $this->l('Save'),
			'lang'                     => $this->lang,
		);

		$db = Db::getInstance();
		$msg_status = $this->l('Status for %s payments');
		$desc_status = $this->l('%s payments get status "%s"');
		$msg_mail = $this->l('Send mails when %s');
		$desc_mail = $this->l('Send mails when transaction status becomes %s?');
		foreach ($this->statuses as $name => $val)
		{
			$val                          = (int) $val;
			$data['msg_status_' . $name]  = sprintf($msg_status, $this->lang[$name]);
			$data['desc_status_' . $name] = ucfirst(sprintf($desc_status,
					$this->lang[$name],
					$db->getValue('SELECT `name` FROM `' . _DB_PREFIX_ . 'order_state_lang` WHERE `id_order_state` = ' . (int) $val . ' AND `id_lang` = ' . (int) $lang)
				));
			$data['val_status_' . $name]  = $val;
			$data['msg_mail_' . $name]    = sprintf($msg_mail, $this->lang[$name]);
			$data['desc_mail_' . $name]   = sprintf($desc_mail, $this->lang[$name]);
			$data['val_mail_' . $name]    = $this->getConfigValue('MOLLIE_MAIL_WHEN_' . strtoupper($name));
			$data['statuses'][]           = $name;
		}

		$this->context->smarty->assign($data);

		return $this->display(__FILE__, 'mollie_config.tpl');
	}

	/**
	 * @param $field
	 * @param $default_value
	 * @return bool
	 */
	public function initConfigValue($field, $default_value)
	{
		return Configuration::updateValue($field, (Configuration::get($field) !== FALSE) ? Configuration::get($field) : $default_value);
	}

	/**
	 * @param $field
	 * @return bool
	 */
	public function deleteConfigValue($field)
	{
		return Configuration::deleteByName($field);
	}

	/**
	 * @param $field
	 * @param $value
	 * @return bool
	 */
	public function updateConfigValue($field, $value)
	{
		return Configuration::updateValue($field, $value);
	}

	/**
	 * @param $field
	 * @return mixed
	 */
	public function getConfigValue($field)
	{
		return Configuration::get($field);
	}


	/**
	 * @param string $str
	 * @return string
	 */
	public function lang($str)
	{
		if (array_key_exists($str, $this->lang))
		{
			return $this->lang[$str];
		}
		return $str;
	}

	/**
	 * @param $order_id
	 * @param $status
	 * @return OrderHistory
	 */
	public function setOrderStatus($order_id, $status)
	{
		$status_id = (int)$this->statuses[$status];
		$history = new OrderHistory();
		$history->id_order = $order_id;
		$history->id_order_state = $status_id;
		$history->changeIdOrderState($status_id, $order_id);

		if ($this->getConfigValue('MOLLIE_MAIL_WHEN_' . strtoupper($status)))
		{
			$history->addWithemail();
		}
		else
		{
			$history->add();
		}

		return $history;
	}

	/**
	 * @param $order_id
	 * @return array
	 */

	public function getPaymentBy($column,$id)
	{
		$paid_payment = Db::getInstance()->getRow(
			sprintf(
				'SELECT * FROM `%s` WHERE `%s` = \'%s\' AND bank_status = \'%s\'',
				_DB_PREFIX_ . 'mollie_payments',
				$column,
				$id,
				Mollie_API_Object_Payment::STATUS_PAID
			)
		);

		if ($paid_payment)
		{
			return $paid_payment;
		}

		$non_paid_payment = Db::getInstance()->getRow(
			sprintf(
				'SELECT * FROM `%s` WHERE `%s` = \'%s\' ORDER BY created_at DESC',
				_DB_PREFIX_ . 'mollie_payments',
				$column,
				$id
			)
		);

		return $non_paid_payment;
	}

	/**
	 * @param array $payscreen_locale_options
	 * @param array $image_options
	 * @param array $issuer_options
	 * @param array $logger_options
	 * @return string
	 */
	protected function _getSaveResult(array $payscreen_locale_options = array(), array $image_options = array(), array $issuer_options = array(), array $logger_options = array())
	{
		$errors = array();
		if (!empty($_POST['Mollie_Api_Key']) && strpos($_POST['Mollie_Api_Key'], 'live') !== 0 && strpos($_POST['Mollie_Api_Key'], 'test') !== 0)
		{
			$errors[] = $this->l('The API key needs to start with test or live.');
		}
		if (!in_array($_POST['Mollie_Paymentscreen_Locale'], $payscreen_locale_options))
		{
			$errors[] = $this->l('Invalid locale setting.');
		}
		if (!in_array($_POST['Mollie_Images'], $image_options))
		{
			$errors[] = $this->l('Invalid image setting.');
		}
		if (!in_array($_POST['Mollie_Issuers'], $issuer_options))
		{
			$errors[] = $this->l('Invalid issuer setting.');
		}
		if (!isset($_POST['Mollie_Css']))
		{
			$_POST['Mollie_Css'] = '';
		}
		if (!in_array($_POST['Mollie_Logger'], $logger_options))
		{
			$errors[] = $this->l('Invalid debug log setting.');
		}
		if (!isset($_POST['Mollie_Errors']))
		{
			$_POST['Mollie_Errors'] = FALSE;
		}
		else
		{
			$_POST['Mollie_Errors'] = ($_POST['Mollie_Errors'] == 1);
		}
		foreach ($this->statuses as $name => $val)
		{
			if (!is_numeric($_POST['Mollie_Status_' . $name]))
			{
				$errors[] = ucfirst($name) . ' status must be numeric.';
			}
		}

		if (empty($errors))
		{
			$this->updateConfigValue('MOLLIE_API_KEY', $_POST['Mollie_Api_Key']);
			$this->updateConfigValue('MOLLIE_DESCRIPTION', $_POST['Mollie_Description']);
			$this->updateConfigValue('MOLLIE_PAYMENTSCREEN_LOCALE', $_POST['Mollie_Paymentscreen_Locale']);
			$this->updateConfigValue('MOLLIE_IMAGES', $_POST['Mollie_Images']);
			$this->updateConfigValue('MOLLIE_ISSUERS', $_POST['Mollie_Issuers']);
			$this->updateConfigValue('MOLLIE_CSS', $_POST['Mollie_Css']);
			$this->updateConfigValue('MOLLIE_DISPLAY_ERRORS', (int) $_POST['Mollie_Errors']);
			$this->updateConfigValue('MOLLIE_DEBUG_LOG', (int) $_POST['Mollie_Logger']);

			foreach ($this->statuses as $name => $old)
			{
				$new                   = (int) $_POST['Mollie_Status_' . $name];
				$this->statuses[$name] = $new;
				$this->updateConfigValue('MOLLIE_STATUS_' . strtoupper($name), $new);

				if ($name != Mollie_API_Object_Payment::STATUS_OPEN)
				{
					$this->updateConfigValue(
						'MOLLIE_MAIL_WHEN_' . strtoupper($name),
						!empty($_POST['Mollie_Mail_When_' . $name]) ? TRUE : FALSE
					);
				}
			}
			$result_msg = $this->l('The configuration has been saved!');
		}
		else
		{
			$result_msg = 'The configuration could not be saved:<br /> - ' . implode('<br /> - ', $errors);
		}
		return $result_msg;
	}


	/**
	 * @param string $url
	 * @return string
	 */
	protected function _getUpdateMessage($url)
	{
		$update_message = '';
		$update_xml = $this->_getUpdateXML($url);
		if ($update_xml === FALSE)
		{
			$update_message = $this->l('Warning: Could not retrieve update xml file from github.');
		}
		else
		{
			/** @var SimpleXMLElement $tags */
			$tags = new SimpleXMLElement($update_xml);
			if (!empty($tags) && isset($tags->entry, $tags->entry[0], $tags->entry[0]->id))
			{
				$title = $tags->entry[0]->id;
				$latest_version = preg_replace("/[^0-9,.]/", "", substr($title, strrpos($title, '/')));
				if (!version_compare($this->version, $latest_version, '>='))
				{
					$update_message = sprintf(
						'<a href="%s/releases">' . $this->l('You are currently using version %s. We strongly recommend you to upgrade to the new version %s!') . '</a>',
						$url, $this->version, $latest_version
					);
				}
			}
			else
			{
				$update_message = $this->l('Warning: Update xml file from github follows an unexpected format.');
			}
		}

		return $update_message;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	protected function _getUpdateXML($url)
	{
		return @file_get_contents($url . '/releases.atom');
	}


	/**
	 * @param int $order_id
	 * @param string $transaction_id
	 * @return string
	 */
	protected function _doRefund($order_id, $transaction_id)
	{
		try
		{
			$payment = $this->api->payments->get($transaction_id);
			$this->api->payments->refund($payment);
		}
		catch (Mollie_API_Exception $e)
		{
			return array(
				'status'      => 'fail',
				'msg_fail'    => $this->lang('The order could not be refunded!'),
				'msg_details' => $this->lang('Reason:') . ' ' . $e->getMessage(),
			);
		}

		// Tell status to shop
		$this->setOrderStatus($order_id, Mollie_API_Object_Payment::STATUS_REFUNDED);

		// Save status in mollie_payments table
		$update_data = array(
			'updated_at' => date("Y-m-d H:i:s"),
			'bank_status' => Mollie_API_Object_Payment::STATUS_REFUNDED,
		);

		Db::getInstance()->update('mollie_payments', $update_data, '`order_id` = ' . (int) $order_id);

		return array(
			'status'      => 'success',
			'msg_success' => $this->lang('The order has been refunded!'),
			'msg_details' => $this->lang('Mollie B.V. will transfer the money back to the customer on the next business day.'),
		);
	}

	/**
	 * @return array
	 */
	protected function _getIssuerList()
	{
		$issuers = $this->api->issuers->all();

		$issuer_list = array();
		foreach ($issuers as $issuer)
		{
			$issuer_list[$issuer->method][$issuer->id] = $issuer->name;
		}
		return $issuer_list;
	}

	protected function _addCSSFile($file = null)
	{
		if (is_null($file))
		{
			$file = $this->getConfigValue('MOLLIE_CSS');
		}

		if (empty($file))
		{
			if (strpos(_PS_THEME_DIR_, '/default-bootstrap/') !== FALSE)
			{
				// Use a modified css file for the new 1.6 default layout
				$file = $this->_path . 'views/css/mollie_bootstrap.css';
			}
			else
			{
				// Use default css file
				$file = $this->_path . 'views/css/mollie.css';
			}
		}
		else
		{
			// Use a custom css file
			$file = str_replace('{BASE}', _PS_BASE_URL_, $file);
			$file = str_replace('{THEME}', _PS_THEME_DIR_, $file);
			$file = str_replace('{CSS}', _PS_CSS_DIR_, $file);
			$file = str_replace('{MOBILE}', _THEME_MOBILE_DIR_, $file);
			$file = str_replace('{MOBILE_CSS}', _THEME_MOBILE_CSS_DIR_, $file);
			$file = str_replace('{OVERRIDE}', _PS_THEME_OVERRIDE_DIR_, $file);
		}
		$this->context->controller->addCSS($file);
	}

	// Hooks

	/**
	 */
	public function hookDisplayHeader()
	{
		$this->_addCSSFile($this->getConfigValue('MOLLIE_CSS'));
	}

	public function hookDisplayBackOfficeHeader()
	{
		$this->_addCSSFile($this->getConfigValue('MOLLIE_CSS'));
	}

	/**
	 * @param $params
	 * @return string
	 */
	public function hookDisplayAdminOrder($params)
	{
		$mollie_data = Db::getInstance()->getRow(sprintf(
				'SELECT * FROM `%s` WHERE `order_id` = %s;',
				_DB_PREFIX_ . 'mollie_payments',
				(int) $params['id_order']
			));

		// Do not show refund option if it's not a successfully paid Mollie transaction
		if ($mollie_data === FALSE || $mollie_data['bank_status'] !== Mollie_API_Object_Payment::STATUS_PAID)
		{
			return '';
		}

		if (Tools::isSubmit('Mollie_Refund'))
		{
			$tpl_data = $this->_doRefund($mollie_data['order_id'], $mollie_data['transaction_id']);
		}
		else
		{
			$tpl_data = array(
				'status'          => 'form',
				'msg_button'      => $this->lang['Refund this order'],
				'msg_description' => sprintf($this->lang['Refund order #%d through the Mollie API.'], (int) $mollie_data['order_id']),
			);
		}

		$tpl_data['msg_title'] = $this->lang['Mollie refund'];
		$tpl_data['img_src'] = $this->_path . 'logo_small.png';
		$this->smarty->assign($tpl_data);
		return $this->display(__FILE__, 'mollie_refund.tpl');
	}

	/**
	 * EU Advanced Compliance module (prestahop module) Advanced Checkout option enabled
	 * @param $params
	 * @return array|void
	 */
	public function hookDisplayPaymentEU($params)
	{
		if (!Currency::exists('EUR', 0))
		{
			return;
		}

		try {
			$methods = $this->api->methods->all();
		} catch (Mollie_API_Exception $e) {
			$methods = array();

			if ($this->getConfigValue('MOLLIE_DEBUG_LOG') == self::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . ' said: ' . $e->getMessage(), Mollie::ERROR);
			}
			
			return;
		}

		$payment_options = array();
		foreach($methods as $method)
		{
			$payment_options[] = array(
				'cta_text' => $this->lang[$method->description],
				'logo' => $method->image->normal,
				'action' => $this->context->link->getModuleLink('mollie', 'payment', array('method' => $method->id), true)
			);
		}

		return $payment_options;
	}

	/**
	 * @return string
	 */
	public function hookDisplayPayment()
	{
		if (!Currency::exists('EUR', 0))
		{
			return	'<p class="payment_module" style="color:red;">' .
			$this->l('Mollie Payment Methods are only available when Euros are activated.') .
			'</p>';
		}

		$issuer_setting = $this->getConfigValue('MOLLIE_ISSUERS');

		try {
			$methods = $this->api->methods->all();
			$issuer_list = in_array($issuer_setting, array(self::ISSUERS_ALWAYS_VISIBLE, self::ISSUERS_ON_CLICK)) ? $this->_getIssuerList() : array();
		} catch (Mollie_API_Exception $e) {
			$methods = array();
			$issuer_list = array();

			if ($this->getConfigValue('MOLLIE_DEBUG_LOG') == self::DEBUG_LOG_ERRORS)
			{
				Logger::addLog(__METHOD__ . ' said: ' . $e->getMessage(), Mollie::ERROR);
			}
			if ($this->getConfigValue('MOLLIE_DISPLAY_ERRORS'))
			{
				return
					'<p class="payment_module" style="color:red;">' .
					$e->getMessage() .
					'</p>'
					;
			}
		}

		$this->smarty->assign(array(
				'methods'        => $methods,
				'issuers'        => $issuer_list,
				'issuer_setting' => $issuer_setting,
				'images'         => $this->getConfigValue('MOLLIE_IMAGES'),
				'warning'        => $this->warning,
				'msg_pay_with'   => $this->lang['Pay with %s'],
				'msg_bankselect' => $this->lang['Select your bank:'],
				'module'         => $this,
			));

		return $this->display(__FILE__, 'mollie_methods.tpl');
	}

	public function hookDisplayPaymentTop()
	{
		$payment = $this->getPaymentBy('cart_id',(int)$this->context->cart->id);
		if ($payment && $payment['bank_status'] == Mollie_API_Object_Payment::STATUS_CANCELLED)
		{
			return '<h4>'.$this->lang('You have cancelled your payment.').'</h4>';
		}
	}

	public function hookDisplayOrderConfirmation()
	{
		$payment = $this->getPaymentBy('cart_id',(int)Tools::getValue('id_cart'));
		if ($payment && $payment['bank_status'] == Mollie_API_Object_Payment::STATUS_PAID)
		{
			return '<h4>'.$this->lang('Thank you. Your payment has been received.').'</h4>';
		}
	}

	public function addCartIdChangePrimaryKey()
	{
		$sql = sprintf('
			ALTER TABLE `%1$s` DROP PRIMARY KEY;
			ALTER TABLE `%1$s` ADD PRIMARY KEY (transaction_id),
				ADD COLUMN `cart_id` INT(64),
				ADD KEY (cart_id);',
			_DB_PREFIX_ . 'mollie_payments');

		if (!Db::getInstance()->execute($sql))
		{
			$this->_errors[] = 'Database error: ' . Db::getInstance()->getMsgError();
			return FALSE;
		}

		return TRUE;
	}

}
