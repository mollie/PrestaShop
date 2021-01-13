<?php

namespace Mollie\Service\Content;

use Mollie\Builder\TemplateBuilderInterface;
use Smarty;

class SmartyTemplateParser implements TemplateParserInterface
{
	/**
	 * @param Smarty $smarty
	 * @param TemplateBuilderInterface $templateBuilder
	 * @param string $templatePath
	 *
	 * @return string
	 */
	public function parseTemplate(Smarty $smarty, TemplateBuilderInterface $templateBuilder, $templatePath)
	{
		$smarty->assign($templateBuilder->buildParams());

		return $smarty->fetch($templatePath) ?: '';
	}
}
