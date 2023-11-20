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
