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

namespace Mollie\Handler\ErrorHandler;

use Configuration;
use Exception;
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
     * @var ?Raven_Client
     */
    protected $client;

    /**
     * @var ErrorHandler
     */
    private static $instance;

    public function __construct($module)
    {
        /** @var Env $env */
        $env = $module->getService(Env::class);

        try {
            $this->client = new ModuleFilteredRavenClient(
                Config::SENTRY_KEY,
                [
                    'level' => Raven_Client::ERROR,
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
            $this->client->set_user_data($this->getServerVariable('SERVER_NAME'), Configuration::get('PS_SHOP_EMAIL'));
        } catch (Exception $e) {
            return;
        }

        // We use realpath to get errors even if module is behind a symbolic link
        $this->client->setAppPath(realpath(_PS_MODULE_DIR_ . $module->name . '/'));

        $this->client->setExcludedAppPaths([
            realpath(_PS_MODULE_DIR_ . $module->name . '/vendor/'),
        ]);
        // Useless as it will exclude everything even if specified in the app path
        //$this->client->setExcludedAppPaths([_PS_ROOT_DIR_]);
        $this->client->install();
    }

    /**
     * @param Exception $error
     * @param mixed $code
     * @param bool|null $throw
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle($error, $code = null, $throw = true)
    {
        if (!$this->client) {
            return;
        }

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
        /** @var Mollie */
        $module = Module::getInstanceByName('mollie');

        if (self::$instance === null) {
            self::$instance = new ErrorHandler($module);
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    private function __clone()
    {
    }

    private function getServerVariable($key)
    {
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return '';
    }
}
