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
use Mollie;
use Mollie\Config\Config;
use Mollie\Config\Env;
use Mollie\Factory\ModuleFactory;
use Sentry\ClientBuilder;
use Sentry\ClientInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\UserDataBag;

/**
 * Handle Error.
 */
class ErrorHandler
{
    /** @var ErrorHandler */
    private static $instance;
    /** @var ClientInterface */
    private $client;

    /** @var Scope */
    private $exceptionContext;

    public function __construct(Mollie $module, Env $env = null)
    {
        /* We need to add this check and make env = null because when upgrading module the old constructor logic is called, and it breaks upgrade */
        if (!$env || !class_exists('Sentry\ClientBuilder')) {
            return;
        }

        //TODO in PS8 sentry_env is not passed for some reason, fix this.
        $client = ClientBuilder::create([
            'dsn' => Config::SENTRY_KEY,
            'release' => $module->version,
            'environment' => $env->get('SENTRY_ENV'),
            'max_breadcrumbs' => 50,
        ]);

        $client->getOptions()->setBeforeSendCallback(function ($event) use ($module) {
            if ($this->shouldSkipError($event, $module)) {
                return null;
            }

            return $event;
        });

        $userData = new UserDataBag();

        $userData->setId($_SERVER['SERVER_NAME']);
        $userData->setEmail(Configuration::get('PS_SHOP_EMAIL'));

        $hub = SentrySdk::getCurrentHub();

        $hub->configureScope(function ($scope) use ($userData) {
            $scope->setUser($userData);
        });

        $client->getOptions()->setInAppIncludedPaths([
            realpath(_PS_MODULE_DIR_ . $module->name . '/'),
        ]);

        $client->getOptions()->setInAppExcludedPaths([
            realpath(_PS_MODULE_DIR_ . $module->name . '/vendor/'),
        ]);

        $this->client = $client->getClient();

        $scope = new Scope();

        $scope->setTags([
            'mollie_version' => $module->version,
            'prestashop_version' => _PS_VERSION_,
            'mollie_is_enabled' => (string) Mollie::isEnabled('mollie'),
            'mollie_is_installed' => (string) Mollie::isInstalled('mollie'), //TODO this is deprecated since 1.7, rewrite someday
        ]);

        $this->exceptionContext = $scope;
    }

    /**
     * @throws Exception
     */
    public function handle(Exception $error, ?int $code = null, ?bool $throw = true): void
    {
        $this->client->captureException($error, $this->exceptionContext);

        if ($code && true === $throw) {
            http_response_code($code);
            throw $error;
        }
    }

    public static function getInstance(Mollie $module = null): ErrorHandler
    {
        if (!$module) {
            $module = (new ModuleFactory())->getModule();
        }

        if (self::$instance === null) {
            self::$instance = new ErrorHandler($module, new Env());
        }

        return self::$instance;
    }

    private function shouldSkipError(\Sentry\Event $event, Mollie $module): bool
    {
        $result = true;

        foreach ($event->getExceptions() as $exception) {
            if (!$exception->getStacktrace()) {
                continue;
            }

            foreach ($exception->getStacktrace()->getFrames() as $frame) {
                $filePath = $frame->getAbsoluteFilePath();

                if (!$filePath) {
                    continue;
                }

                if (strpos($filePath, '/' . $module->name . '/') !== false) {
                    $result = false;
                    break;
                }
            }

            if (!$result) {
                break;
            }
        }

        return $result;
    }
}
