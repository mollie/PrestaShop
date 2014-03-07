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

if (!defined('_PS_VERSION_'))
	exit;

class Mollie extends PaymentModule
{
	/** @var Mollie_API_Client|null */
	public $api = null;
	public $statuses = array();
	public $mollie_version = '1.0.0';

	public function __construct()
	{
		$this->name = 'mollie';
		$this->tab = 'payments_gateways';
		$this->version = '0.0.1';
		$this->author = 'Mollie BV';
		$this->need_instance = TRUE;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '2');
		$this->dependencies = array('blockcart');

		parent::__construct();

		$this->displayName = $this->l('Mollie Payment Module');
		$this->description = $this->l('Mollie Payments');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall the Mollie Payment Module?', 'mollie');

		require_once(__DIR__ . '/lib/src/Mollie/API/Autoloader.php');

		try
		{
			$this->api = new Mollie_API_Client;
			$this->api->setApiKey(Configuration::get('MOLLIE_API_KEY'));
			$this->api->addVersionString('Prestashop/' . defined(_PS_INSTALL_VERSION_) ? _PS_INSTALL_VERSION_ : 'Unknown');
			$this->api->addVersionString('Mollie_Prestashop/' . $this->mollie_version);
			$this->methods = $this->api->methods->all();
		}
		catch (Mollie_API_Exception $e)
		{
			$this->warning = $this->l('Payment error:', 'mollie') . $e->getMessage();
		}

		$this->statuses = array(
			Mollie_API_Object_Payment::STATUS_OPEN		=> Configuration::get('MOLLIE_STATUS_OPEN'),
			Mollie_API_Object_Payment::STATUS_PAID		=> Configuration::get('MOLLIE_STATUS_PAID'),
			Mollie_API_Object_Payment::STATUS_CANCELLED	=> Configuration::get('MOLLIE_STATUS_CANCELLED'),
			Mollie_API_Object_Payment::STATUS_EXPIRED	=> Configuration::get('MOLLIE_STATUS_EXPIRED'),
		);
	}


	/**
	 * Installs the Mollie Payments Module
	 * @return bool
	 */
	public function install()
	{
		if (
			parent::install() &&
			$this->registerHook('displayPayment') &&
			$this->_initConfigValue('MOLLIE_API_KEY', '') &&
			$this->_initConfigValue('MOLLIE_DESCRIPTION', 'Order %') &&
			$this->_initConfigValue('MOLLIE_IMAGES', 'normal') &&
			$this->_initConfigValue('MOLLIE_STATUS_OPEN', 3) &&
			$this->_initConfigValue('MOLLIE_STATUS_PAID', 2) &&
			$this->_initConfigValue('MOLLIE_STATUS_CANCELLED', 6) &&
			$this->_initConfigValue('MOLLIE_STATUS_EXPIRED', 8) &&
			$this->_initConfigValue('MOLLIE_MAIL_WHEN_OPEN', FALSE) &&
			$this->_initConfigValue('MOLLIE_MAIL_WHEN_PAID', TRUE) &&
			$this->_initConfigValue('MOLLIE_MAIL_WHEN_CANCELLED', FALSE) &&
			$this->_initConfigValue('MOLLIE_MAIL_WHEN_EXPIRED', FALSE)
		)
		{
			$sql = sprintf('
				CREATE TABLE IF NOT EXISTS `%s` (
					`order_id` INT(16) NOT NULL PRIMARY KEY,
					`method` VARCHAR(64) NOT NULL,
					`transaction_id` VARCHAR(32) NOT NULL,
					`bank_account` VARCHAR(64) NOT NULL,
					`bank_status` VARCHAR(20) NOT NULL,
					`created_at` DATETIME NOT NULL,
					`updated_at` DATETIME DEFAULT NULL,
					UNIQUE KEY `transaction_id` (`transaction_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			', _DB_PREFIX_ . 'mollie_payments');

			if (!Db::getInstance()->execute($sql))
			{
				$this->_errors[] = Db::getInstance()->getMsgError();
				return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function uninstall()
	{
		if (
			Configuration::deleteByName('MOLLIE_API_KEY') &&
			Configuration::deleteByName('MOLLIE_DESCRIPTION')
		)
		{
			$sql = sprintf('
				DROP TABLE IF EXISTS `%s`;
			', _DB_PREFIX_ . 'mollie_payments');

			if (Db::getInstance()->execute($sql))
			{
				return parent::uninstall();
			}
		}
		return FALSE;
	}

	/**
	 * @return mixed
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * @param $field
	 * @param $default_value
	 * @return bool
	 */
	public function _initConfigValue($field, $default_value)
	{
		return Configuration::updateValue($field, Configuration::get($field) !== FALSE ? Configuration::get($field) : $default_value);
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		global $cookie;
		$lang = isset($cookie->id_lang) ? (int) $cookie->id_lang : 1;

		$result_msg = '';
		$image_options = array('big', 'normal', 'hide');

		if (Tools::isSubmit('Mollie_Config_Save'))
		{
			$errors = array();
			if (strpos($_POST['Mollie_Api_Key'], 'live') !== 0 && strpos($_POST['Mollie_Api_Key'], 'test') !== 0)
			{
				$errors[] = $this->l('The API key needs to start with test or live', 'mollie');
			}
			if (!in_array($_POST['Mollie_Images'], $image_options))
			{
				$errors[] = $this->l('Image setting must be BIG, NORMAL or HIDE.', 'mollie');
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
				Configuration::updateValue('MOLLIE_API_KEY', $_POST['Mollie_Api_Key']);
				Configuration::updateValue('MOLLIE_DESCRIPTION', $_POST['Mollie_Description']);
				Configuration::updateValue('MOLLIE_IMAGES', $_POST['Mollie_Images']);
				foreach ($this->statuses as $name => $old)
				{
					$new = (int) $_POST['Mollie_Status_' . $name];
					$this->statuses[$name] = $new;
					Configuration::updateValue('MOLLIE_STATUS_'.strtoupper($name), $new);
					Configuration::updateValue('MOLLIE_MAIL_WHEN_'.strtoupper($name), !empty($_POST['Mollie_Mail_When_' . $name]) ? TRUE : FALSE);
				}
				$result_msg = $this->l('The configuration has been saved!', 'mollie');
			}
			else
			{
				$result_msg = 'The configuration could not be saved:<br /> - ' . implode('<br /> - ', $errors);
			}
		}

		$data = array(
			'form_action'		=> Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
			'config_title'		=> $this->l('Mollie Configuration', 'mollie'),
			'all_statuses'		=> OrderState::getOrderStates($lang),
			'image_options'		=> $image_options,
			'msg_result'		=> $result_msg,
			'msg_api_key'		=> $this->l('API key:', 'mollie'),
			'msg_desc'			=> $this->l('Description:', 'mollie'),
			'msg_save'			=> $this->l('Save settings:', 'mollie'),
			'msg_images'		=> $this->l('Images:', 'mollie'),
			'desc_api_key'		=> $this->l('You can find your API key in your <a href="https://www.mollie.nl/beheer/account/profielen/">Mollie Profile</a>; it starts with test or live.', 'mollie'),
			'desc_desc'			=> $this->l('Enter a description here. Note: Payment methods may have a character limit, best keep the description under 29 characters.', 'mollie'),
			'desc_images'		=> $this->l('Show big, normal or no payment method logos on checkout.'),
			'val_api_key'		=> Configuration::get('MOLLIE_API_KEY'),
			'val_desc'			=> Configuration::get('MOLLIE_DESCRIPTION'),
			'val_images'		=> Configuration::get('MOLLIE_IMAGES'),
			'val_save'			=> $this->l('Save', 'mollie'),
		);

		$db = Db::getInstance();
		foreach ($this->statuses as $name => $val)
		{
			$val = (int) $val;
			$data['msg_status_' . $name] = "Status for $name payments";
			$data['desc_status_' . $name] = ucfirst($name) . ' payments get status "' . $db->getValue('SELECT `name` FROM `'._DB_PREFIX_.'order_state_lang` WHERE `id_order_state` = ' . $val . ' AND `id_lang` = ' . $lang) . '"';
			$data['val_status_' . $name] = $val;
			$data['msg_mail_' . $name] = "Send mails when " . $name;
			$data['desc_mail_' . $name] = "Send mails when transaction status becomes " . $name . "?";
			$data['val_mail_' . $name] = Configuration::get('MOLLIE_MAIL_WHEN_'.strtoupper($name));
			$data['statuses'][] = $name;
		}

		$this->context->smarty->assign($data);
		return $this->display(__FILE__, 'mollie_config.tpl');
	}


	// Hooks

	/**
	 * @return string
	 */
	public function hookDisplayPayment()
	{
		if (!Currency::exists('EUR'))
		{
			return '<p class="payment_module" style="color:red;">' . $this->l('Mollie Payment Methods are only available when Euros are activated.', 'mollie') . '</p>';
		}

		try
		{
			$methods = $this->api->methods->all();
			$issuers = $this->api->issuers->all();
		}
		catch (Exception $e)
		{
			$methods = array();
			$this->warning = $e->getMessage();
		}

		$issuer_list = array();
		foreach ($issuers as $issuer)
		{
			$issuer_list[$issuer->method][$issuer->id] = $issuer->name;
		}

		$this->smarty->assign(array(
			'methods'	=> $methods,
			'issuers'	=> $issuer_list,
			'images'	=> Configuration::get('MOLLIE_IMAGES')
		));

		return $this->display(__FILE__, 'mollie_methods.tpl');
	}
}