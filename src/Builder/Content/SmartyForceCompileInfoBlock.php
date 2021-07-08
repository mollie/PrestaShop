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

namespace Mollie\Builder\Content;

use Mollie;
use Mollie\Builder\TemplateBuilderInterface;

class SmartyForceCompileInfoBlock implements TemplateBuilderInterface
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
            'settingKey' => $this->module->l('Template compilation'),
            'settingValue' => $this->module->l('Never recompile template files'),
            'settingsPage' => \Mollie\Utility\MenuLocationUtility::getMenuLocation('AdminPerformance'),
        ];
    }
}
