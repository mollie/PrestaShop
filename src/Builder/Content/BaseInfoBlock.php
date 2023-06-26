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

use Configuration;
use Context;
use Mollie;
use Mollie\Builder\TemplateBuilderInterface;

class BaseInfoBlock implements TemplateBuilderInterface
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
            'path' => $this->module->getPathUri(),
            'payscreen_locale_value' => Configuration::get(Mollie\Config\Config::MOLLIE_PAYMENTSCREEN_LOCALE),
            'val_images' => Configuration::get(Mollie\Config\Config::MOLLIE_IMAGES),
            'val_css' => Configuration::get(Mollie\Config\Config::MOLLIE_CSS),
            'val_errors' => Configuration::get(Mollie\Config\Config::MOLLIE_DISPLAY_ERRORS),
            'val_logger' => Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG),
            'link' => Context::getContext()->link,
            'module_dir' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/',
            'publicPath' => __PS_BASE_URI__ . 'modules/' . basename(__FILE__, '.php') . '/views/js/dist/',
        ];
    }
}
