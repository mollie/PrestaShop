<?php

namespace Mollie\Builder\Content;

use Configuration;
use Context;
use Mollie;
use Mollie\Builder\TemplateBuilderInterface;

class BaseInfoBlock implements TemplateBuilderInterface
{
	/**
	 * @var Mollie
	 */
	private $module;

	public function __construct(Mollie $module)
	{
		$this->module = $module;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildParams()
	{
		return [
			'title_status' => $this->module->l('%s statuses:'),
			'title_visual' => $this->module->l('Visual settings:'),
			'title_debug' => $this->module->l('Debug info:'),
			'path' => $this->module->getPathUri(),
			'payscreen_locale_value' => Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE),
			'val_images' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES),
			'val_issuers' => Configuration::get(Mollie\Config\Config::MOLLIE_ISSUERS),
			'val_css' => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
			'val_errors' => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
			'val_logger' => Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG),
			'val_save' => $this->module->l('Save'),
			'description_message' => $this->module->l('Description cannot be empty'),
			'Profile_id_message' => $this->module->l('Wrong profile ID'),
			'link' => Context::getContext()->link,
			'module_dir' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/',
			'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
		];
	}
}
