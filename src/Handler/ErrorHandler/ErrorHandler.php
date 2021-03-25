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

namespace Mollie\Handler\ErrorHandler;

use Module;
use Mollie;
use Mollie\Config\Config;
use Mollie\Config\Env;
use Raven_Client;

/**
 * Handle Error.
 */
class ErrorHandler
{
	/**
	 * @var Raven_Client
	 */
	protected $client;

	/**
	 * @var ErrorHandler
	 */
	private static $instance;

	public function __construct()
	{
		/** @var Mollie */
		$module = Module::getInstanceByName('mollie');

		/** @var Env $env */
		$env = $module->getMollieContainer(Env::class);

		$this->client = new Raven_Client(
			Config::SENTRY_KEY,
			[
				'level' => 'warning',
				'tags' => [
					'php_version' => phpversion(),
					'mollie_version' => $module->version,
					'prestashop_version' => _PS_VERSION_,
					'mollie_is_enabled' => \Module::isEnabled('mollie'),
					'mollie_is_installed' => \Module::isInstalled('mollie'),
					'env' => $env->get('SENTRY_ENV'),
				],
			]
		);
	}

	/**
	 * @param \Exception $error
	 * @param mixed $code
	 * @param bool|null $throw
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function handle($error, $code = null, $throw = true)
	{
		$this->client->captureException($error);
		if ($code && true === $throw) {
			http_response_code($code);
			throw $error;
		}
	}

	/**
	 * @return ErrorHandler
	 */
	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new ErrorHandler();
		}

		return self::$instance;
	}

	/**
	 * @return void
	 */
	private function __clone()
	{
	}
}
