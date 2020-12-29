<?php

namespace Mollie\Builder\Content;

use Mollie;
use Mollie\Builder\TemplateBuilderInterface;
use Mollie\Utility\MenuLocationUtility;

class RoundingModeInfoBlock implements TemplateBuilderInterface
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
			'settingKey' => $this->module->l('Rounding mode'),
			'settingValue' => $this->module->l('Round up away from zero, when it is half way there (recommended)'),
			'settingsPage' => MenuLocationUtility::getMenuLocation('AdminPreferences'),
		];
	}
}
