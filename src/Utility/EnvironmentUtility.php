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

class EnvironmentUtility
{
    public static function getApiKey()
    {
        $environment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
        $apiKeyConfig = Config::ENVIRONMENT_LIVE === (int) $environment ?
            Config::MOLLIE_API_KEY : Config::MOLLIE_API_KEY_TEST;

        return Configuration::get($apiKeyConfig);
    }
}
