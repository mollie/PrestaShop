<?php

namespace Mollie\Builder\Content;

use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Builder\TemplateBuilderInterface;
use Mollie\Service\LanguageService;

class BaseInfoBlock implements TemplateBuilderInterface
{
	/**
	 * @var Mollie
	 */
	private $module;

	/**
	 * @var LanguageService
	 */
	private $languageService;

	/**
	 * @var ConfigurationAdapter
	 */
	private $configurationAdapter;

	/**
	 * @var string
	 */
	private $resultMessages = '';

	public function __construct(
		Mollie $module,
		LanguageService $languageService,
		ConfigurationAdapter $configurationAdapter
	) {
		$this->module = $module;
		$this->languageService = $languageService;
		$this->configurationAdapter = $configurationAdapter;
	}

	public function setResultMessages($resultMessages)
	{
		$this->resultMessages = $resultMessages;

		return $this;
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
			'msg_result' => $this->resultMessages,
			'path' => $this->module->getPathUri(),
			'payscreen_locale_value' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE),
			'val_images' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_IMAGES),
			'val_issuers' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_ISSUERS),
			'val_css' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_CSS),
			'val_errors' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
			'val_qrenabled' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_QRENABLED),
			'val_logger' => $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_DEBUG_LOG),
			'val_save' => $this->module->l('Save'),
			'lang' => $this->languageService->getLang(),
			'logo_url' => $this->module->getPathUri() . 'views/img/mollie_logo.png',
			'webpack_urls' => \Mollie\Utility\UrlPathUtility::getWebpackChunks('app'),
			'description_message' => $this->module->l('Description cannot be empty'),
			'Profile_id_message' => $this->module->l('Wrong profile ID'),
		];
	}
}
