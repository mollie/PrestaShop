<?php

namespace Mollie\Service\Content;

use Mollie\Builder\TemplateBuilderInterface;
use Smarty;

interface TemplateParserInterface
{
	/**
	 * @param Smarty $smarty
	 * @param TemplateBuilderInterface $templateBuilder
	 * @param string $templatePath
	 *
	 * @return string
	 */
	public function parseTemplate(Smarty $smarty, TemplateBuilderInterface $templateBuilder, $templatePath);
}
