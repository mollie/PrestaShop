<?php

namespace Mollie\Builder\Content;

use Mollie;
use Mollie\Builder\TemplateBuilderInterface;

class SmartyCacheInfoBlock implements TemplateBuilderInterface
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
		$this->controllerBlock->setError($this->module->display(__FILE__, 'smarty_error.tpl'));

		return [
			'settingKey' => $this->module->l('Clear cache'),
			'settingValue' => $this->module->l('Never clear cache files'),
			'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPerformance'),
		];
	}
}
