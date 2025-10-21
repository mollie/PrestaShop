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

declare(strict_types=1);

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Builder\ApiTestFeedbackBuilder;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieAuthenticationController extends ModuleAdminController
{
    public const PS_CLOUDSYNC_CDC = 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js';

    const FILE_NAME = 'AdminMollieAuthenticationController';

    /** @var Mollie */
    public $module;

    /** @var ToolsAdapter */
    private $tools;

    /** @var ConfigurationAdapter */
    private $configuration;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->tools = $this->module->getService(ToolsAdapter::class);
        $this->configuration = $this->module->getService(ConfigurationAdapter::class);
    }

    public function init(): void
    {
        parent::init();

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $jsUrl = $this->module->getPathUri() . 'views/js/admin/library/dist/assets/authorization.js?v=' . $this->module->version;
        $this->context->smarty->assign('mollieAuthJsUrl', $jsUrl);

        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/globals.css?v=' . $this->module->version,
            'all',
            null,
            false
        );

        Media::addJsDef([
            'mollieAuthAjaxUrl' => $this->context->link->getAdminLink('AdminMollieAuthentication'),
        ]);

        Media::addJsDef([
            'mollieAuthTranslations' => [
                'mode' => $this->module->l('Mode', self::FILE_NAME),
                'modeDescription' => $this->module->l('Choose operational mode for API.', self::FILE_NAME),
                'live' => $this->module->l('Live', self::FILE_NAME),
                'test' => $this->module->l('Test', self::FILE_NAME),
                'testApiKey' => $this->module->l('Test API Key', self::FILE_NAME),
                'liveApiKey' => $this->module->l('Live API Key', self::FILE_NAME),
                'apiKeyPlaceholder' => $this->module->l('Enter your API key here', self::FILE_NAME),
                'apiKeyDescription' => $this->module->l('Required for connecting to the %s mode.', self::FILE_NAME),
                'connect' => $this->module->l('Connect', self::FILE_NAME),
                'connecting' => $this->module->l('Connecting...', self::FILE_NAME),
                'connected' => $this->module->l('Connected', self::FILE_NAME),
                'connectedSuccessfully' => $this->module->l('Connected successfully!', self::FILE_NAME),
                'show' => $this->module->l('Show', self::FILE_NAME),
                'hide' => $this->module->l('Hide', self::FILE_NAME),
                'whereApiKey' => $this->module->l('Where can I find my API key?', self::FILE_NAME),
                'needHelp' => $this->module->l('Need Help?', self::FILE_NAME),
                'getStarted' => $this->module->l('Get started', self::FILE_NAME),
                'mollieDocumentation' => $this->module->l('Mollie documentation', self::FILE_NAME),
                'paymentsQuestions' => $this->module->l('Payments related questions', self::FILE_NAME),
                'contactMollieSupport' => $this->module->l('Contact Mollie Support', self::FILE_NAME),
                'integrationQuestions' => $this->module->l('Integration questions', self::FILE_NAME),
                'contactModuleDeveloper' => $this->module->l('Contact module developer', self::FILE_NAME),
                'newToMollie' => $this->module->l('New to Mollie?', self::FILE_NAME),
                'createAccount' => $this->module->l('Create a Mollie account', self::FILE_NAME),
                'apiConfiguration' => $this->module->l('API Configuration', self::FILE_NAME),
                'selectModeDescription' => $this->module->l('Select your operational mode and input API keys below.', self::FILE_NAME),
                'connectionFailed' => $this->module->l('Connection failed. Please check your API key.', self::FILE_NAME),
                'failedToLoadSettings' => $this->module->l('Failed to load current settings', self::FILE_NAME),
                'failedToSwitchEnvironment' => $this->module->l('Failed to switch environment', self::FILE_NAME),
                'switchEnvironment' => $this->module->l('Switch Environment', self::FILE_NAME),
                'confirmSwitchEnvironment' => $this->module->l('Are you sure you want to switch to %s environment?', self::FILE_NAME),
                'cancel' => $this->module->l('Cancel', self::FILE_NAME),
                'switchTo' => $this->module->l('Switch to %s', self::FILE_NAME),
            ],
        ]);

        Media::addJsDef([
            'mollieAuthAjaxUrl' => $this->context->link->getAdminLink('AdminMollieAuthentication'),
            'mollieVersion' => $this->module->version
        ]);

        try {
            $this->initCloudSyncAndPsAccounts();
        } catch (Exception $e) {
            $logger->error('Failed to initiate cloud sync and ps accounts', [
                'context' => [],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);
        }

        $this->content .= $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/authentication/authentication.tpl'
        );
    }

    private function initCloudSyncAndPsAccounts(): void
    {
        $mboInstaller = new Prestashop\ModuleLibMboInstaller\DependencyBuilder($this->module);

        if (!$mboInstaller->areDependenciesMet()) {
            $dependencies = $mboInstaller->handleDependencies();
            $this->context->smarty->assign('dependencies', $dependencies);

            $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/dependency_builder.tpl');

            return;
        }

        $this->context->smarty->assign('module_dir', $this->module->getPathUri());
        $moduleManager = PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance()->build();

        /** @var LoggerInterface $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $accountsFacade = $this->module->getService('Mollie.PsAccountsFacade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            try {
                $accountsInstaller = $this->module->getService('Mollie.PsAccountsInstaller');
                $accountsInstaller->install();
                $accountsFacade = $this->module->getService('Mollie.PsAccountsFacade');
                $accountsService = $accountsFacade->getPsAccountsService();
            } catch (Exception $e) {
                $this->context->controller->errors[] = $e->getMessage();

                $logger->error(sprintf('%s - Failed to install ps_accounts', self::FILE_NAME), [
                    'exceptions' => ExceptionUtility::getExceptions($e),
                ]);

                return;
            }
        }

        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->module->name),
            ]);

            // Retrieve Account CDN
            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
        } catch (\Throwable $e) {
            $this->context->controller->errors[] = $e->getMessage();

            $logger->error(sprintf('%s - Failed to load ps accounts CDN', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);
        }

        if ($moduleManager->isInstalled('ps_eventbus')) {
            $eventbusModule = \Module::getInstanceByName('ps_eventbus');
            if ($eventbusModule && version_compare($eventbusModule->version, '1.9.0', '>=')) {
                /** @phpstan-ignore-next-line PHPStan does not recognize the event bus module, so it doesn't know it has getService function */
                $eventbusPresenterService = $eventbusModule->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');

                $this->context->smarty->assign('urlCloudsync', Config::PS_CLOUDSYNC_CDC);
                $this->addJs($this->module->getPathUri() . '/views/js/admin/cloudsync.js');
                Media::addJsDef([
                    'contextPsEventbus' => $eventbusPresenterService->expose($this->module, ['info', 'modules', 'themes']),
                ]);
            }
        }

        $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cloudsync.tpl');

        return;
    }

    public function displayAjax(): void
    {
        if (!$this->tools->isSubmit('ajax')) {
            return;
        }

        $action = $this->tools->getValue('action');

        switch ($action) {
            case 'testApiKeys':
                $this->ajaxTestApiKeys();
                break;
            case 'getCurrentSettings':
                $this->ajaxGetCurrentSettings();
                break;
            case 'saveApiKey':
                $this->ajaxSaveApiKey();
                break;
            case 'switchEnvironment':
                $this->ajaxSwitchEnvironment();
                break;
            default:
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => 'Invalid action',
                ]));
                break;
        }
    }

    private function ajaxTestApiKeys(): void
    {
        $testKey = $this->tools->getValue('testKey');
        $liveKey = $this->tools->getValue('liveKey');

        $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);
        $apiTestFeedbackBuilder->setTestKey($testKey);
        $apiTestFeedbackBuilder->setLiveKey($liveKey);
        $apiKeysTestInfo = $apiTestFeedbackBuilder->buildParams();

        $this->context->smarty->assign($apiKeysTestInfo);
        $this->ajaxRender(json_encode([
            'success' => true,
            'data' => $apiKeysTestInfo,
        ]));
    }

    private function ajaxGetCurrentSettings(): void
    {
        try {
            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);
            $environment = $this->configuration->get(Config::MOLLIE_ENVIRONMENT);

            $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);

            $testKeyValid = false;
            $liveKeyValid = false;

            if ($testApiKey) {
                $testKeyInfo = $apiTestFeedbackBuilder->getApiKeyInfo($testApiKey, true);
                $testKeyValid = $testKeyInfo['status'] && !$testKeyInfo['warning'];
            }

            if ($liveApiKey) {
                $liveKeyInfo = $apiTestFeedbackBuilder->getApiKeyInfo($liveApiKey, false);
                $liveKeyValid = $liveKeyInfo['status'] && !$liveKeyInfo['warning'];
            }

            $isConnected = $environment ? $liveKeyValid : $testKeyValid;

            $this->ajaxRender(json_encode([
                'success' => true,
                'data' => [
                    'test_api_key' => $testApiKey ?: '',
                    'live_api_key' => $liveApiKey ?: '',
                    'environment' => $environment ? 'live' : 'test',
                    'is_configured' => !empty($testApiKey) || !empty($liveApiKey),
                    'is_connected' => $isConnected,
                    'test_key_valid' => $testKeyValid,
                    'live_key_valid' => $liveKeyValid,
                ],
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to load current settings', self::FILE_NAME),
            ]));
        }
    }

    private function ajaxSaveApiKey(): void
    {
        try {
            $apiKey = $this->tools->getValue('api_key');
            $environment = $this->tools->getValue('environment');

            if (!$apiKey || !$environment) {
                throw new MollieException($this->module->l('Missing required parameters', self::FILE_NAME));
            }

            $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);

            $isTestKey = ($environment === 'test');
            $keyInfo = $apiTestFeedbackBuilder->getApiKeyInfo($apiKey, $isTestKey);

            if (!$keyInfo['status']) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => 'API key validation failed: Key does not exist or is invalid',
                ]));

                return;
            }

            if ($keyInfo['warning']) {
                $expectedPrefix = $isTestKey ? 'test_' : 'live_';
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => "API key validation failed: Key must start with '{$expectedPrefix}'",
                ]));

                return;
            }

            $configKey = ($environment === 'live') ? Config::MOLLIE_API_KEY : Config::MOLLIE_API_KEY_TEST;

            $this->configuration->updateValue($configKey, $apiKey);

            $environmentValue = ($environment === 'live') ? Config::ENVIRONMENT_LIVE : Config::ENVIRONMENT_TEST;
            $this->configuration->updateValue(Config::MOLLIE_ENVIRONMENT, $environmentValue);

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => 'API key saved successfully',
                'data' => [
                    'is_connected' => true,
                    'methods' => $keyInfo['methods'] ?? [],
                ],
            ]));
        } catch (MollieException $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to save API key', self::FILE_NAME),
            ]));
        }
    }

    private function ajaxSwitchEnvironment(): void
    {
        try {
            $environment = $this->tools->getValue('environment');

            if (!$environment || !in_array($environment, ['test', 'live'])) {
                throw new MollieException($this->module->l('Invalid environment parameter. Must be "test" or "live"', self::FILE_NAME));
            }

            $environmentValue = ($environment === 'live') ? Config::ENVIRONMENT_LIVE : Config::ENVIRONMENT_TEST;

            $this->configuration->updateValue(Config::MOLLIE_ENVIRONMENT, $environmentValue);

            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);

            $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);

            $isConnected = false;
            $apiKey = '';

            if ($environment === 'live' && $liveApiKey) {
                $keyInfo = $apiTestFeedbackBuilder->getApiKeyInfo($liveApiKey, false);
                $isConnected = $keyInfo['status'] && !$keyInfo['warning'];
                $apiKey = $liveApiKey;
            } elseif ($environment === 'test' && $testApiKey) {
                $keyInfo = $apiTestFeedbackBuilder->getApiKeyInfo($testApiKey, true);
                $isConnected = $keyInfo['status'] && !$keyInfo['warning'];
                $apiKey = $testApiKey;
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => 'Environment switched successfully',
                'data' => [
                    'environment' => $environment,
                    'is_connected' => $isConnected,
                    'api_key' => $apiKey,
                ],
            ]));
        } catch (MollieException $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to switch environment', self::FILE_NAME),
            ]));
        }
    }
}
