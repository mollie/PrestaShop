<?php

namespace Mollie\Builder\Content;

use Context;
use Mollie;
use Mollie\Builder\TemplateBuilderInterface;

class ModuleLinkInfoBlock implements TemplateBuilderInterface
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
			'link' => Context::getContext()->link,
			'module_dir' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/',
			'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
		];
	}
}
