<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service\Content;

use Mollie\Builder\TemplateBuilderInterface;
use Smarty;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SmartyTemplateParser implements TemplateParserInterface
{
    /**
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
