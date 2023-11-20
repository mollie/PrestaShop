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

namespace Mollie\Utility;

use Configuration;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomLogoUtility
{
    /**
     * @param string $methodName
     *
     * @return bool
     */
    public static function isCustomLogoEnabled($methodName)
    {
        switch ($methodName) {
            case 'creditcard':
                return (bool) Configuration::get(Config::MOLLIE_SHOW_CUSTOM_LOGO);
            default:
                return false;
        }
    }
}
