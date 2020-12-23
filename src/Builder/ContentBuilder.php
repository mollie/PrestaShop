<?php

namespace Mollie\Builder;

class ContentBuilder implements \Mollie\Builder\TemplateBuilderInterface
{
	/**
	 * @var array
	 */
	private $templateBlocks = [];

	public function addTemplateBlock(TemplateBuilderInterface $templateBuilder)
	{
		$this->templateBlocks = array_merge($this->templateBlocks, $templateBuilder->buildParams());

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildParams()
	{
		return $this->templateBlocks;
	}
}
