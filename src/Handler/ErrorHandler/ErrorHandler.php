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
use Mollie\Config\Config;
use Mollie\Config\Env;
use Sentry\ClientBuilder;
use Sentry\ClientBuilderInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\UserDataBag;

/**
 * Handle Error.
 */
class ErrorHandler
{
    /** @var ClientBuilderInterface */
    private $client;

    /** @var Scope */
    private $exceptionContext;

    public function __construct($module, Env $env)
    {
        /** @var Env $env */
        $env = $module->getMollieContainer(Env::class);

        $client = ClientBuilder::create([
            'dsn' => Config::SENTRY_KEY,
            'release' => $module->version,
            'environment' => $env->get('SENTRY_ENV'),
            'debug' => false,
            'max_breadcrumbs' => 50
        ]);

        $client->getOptions()->setBeforeSendCallback(function ($exception) use ($module) {
            if ($this->shouldSkipError($exception, $module)) {
                return null;
            }

            return $exception;
        });

        $userData = new UserDataBag();

        $userData->setId($_SERVER['SERVER_NAME']);
        $userData->setEmail(Configuration::get('PS_SHOP_EMAIL'));

        $hub = SentrySdk::getCurrentHub();

        $hub->configureScope(function ($scope) use ($userData) {
            $scope->setUser($userData);
        });

        $client->getOptions()->setInAppIncludedPaths([
            realpath(_PS_MODULE_DIR_ . $module->name . '/')
        ]);

        $client->getOptions()->setInAppExcludedPaths([
            realpath(_PS_MODULE_DIR_ . $module->name . '/vendor/')
        ]);

        $this->client = $client->getClient();

        $scope = new Scope();

        $scope->setTags([
            'mollie_version' => $module->version,
            'prestashop_version' => _PS_VERSION_,
            'mollie_is_enabled' => \Module::isEnabled('mollie'),
            'mollie_is_installed' => \Module::isInstalled('mollie'),
        ]);

        $this->exceptionContext = $scope;
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
    public function handle(Exception $error, ?int $code = null, ?bool $throw = true): void
    {
        if (!$this->client) {
            return;
        }

        $this->client->captureException($error, $this->exceptionContext);

        if ($code && true === $throw) {
            http_response_code($code);
            throw $error;
        }
    }

    /**
     * @return ErrorHandler
     */
    public static function getInstance(): self
    {
        $module = Module::getInstanceByName('mollie');

        return new ErrorHandler($module, new Env());
    }

    private function shouldSkipError(array $data, Module $module): bool
    {
        //NOTE: unsure from where this error is coming from but without request might as well log it.
        if (!isset($data['request'], $data['request']['url'])) {
            return true;
        }

        $parsedUrl = parse_url($data['request']['url']);

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);

            $moduleQueryParameter = isset($query['module']) ? $query['module'] : null;
            $controllerQueryParameter = isset($query['controller']) ? $query['controller'] : null;
        } else {
            $pathParameters = explode('/', $parsedUrl['path']);

            //NOTE only need to check for module, controller should only be on BO and it has query parameters.
            $controllerQueryParameter = null;

            $position = array_search('module', $pathParameters, true);

            if ($position !== false && array_key_exists($position + 1, $pathParameters)) {
                $moduleQueryParameter = $pathParameters[$position + 1];
            } else {
                $moduleQueryParameter = null;
            }
        }

        //NOTE: module parameter is mostly from frontend of PS while controller is from backend.
        $moduleQueryParameterHasModuleName = strtolower($moduleQueryParameter) === strtolower($module->name);
        $controllerQueryParameterHasModuleName = strpos(strtolower($controllerQueryParameter), strtolower($module->name)) !== false;

        //NOTE: at least one should be given, else we will not be sending it.
        if (!$moduleQueryParameter && !$controllerQueryParameter) {
            return false;
        }

        return $moduleQueryParameterHasModuleName || $controllerQueryParameterHasModuleName;
    }
}
