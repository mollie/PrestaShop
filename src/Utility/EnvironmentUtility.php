<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Configuration;
use Mollie\Config\Config;
use Tools;

class EnvironmentUtility
{
	/**
	 * Check if local domain.
	 *
	 * @param string|null $host
	 *
	 * @return bool
	 *
	 * @since 3.3.2
	 */
	public static function isLocalEnvironment($host = null)
	{
		if (!$host) {
			$host = Tools::getHttpHost(false, false, true);
		}
		$hostParts = explode('.', $host);
		$tld = end($hostParts);

		return in_array($tld, ['localhost', 'test', 'dev', 'app', 'local', 'invalid', 'example'])
			|| (filter_var($host, FILTER_VALIDATE_IP)
				&& !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
	}

	public static function getApiKey()
	{
		$environment = Configuration::get(Config::MOLLIE_ENVIRONMENT);
		$apiKeyConfig = Config::ENVIRONMENT_LIVE === (int) $environment ?
			Config::MOLLIE_API_KEY : Config::MOLLIE_API_KEY_TEST;

		return Configuration::get($apiKeyConfig);
	}
}
