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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieAuthenticationController extends ModuleAdminController
{
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

    /**
     * Initialize the authentication page
     */
    public function init(): void
    {
        parent::init();

        //todo use module version after redesign will finish.
        $version = time();

        // Pass JS URL to template for ES module loading
        $jsUrl = $this->module->getPathUri() . 'views/js/admin/library/dist/assets/authorization.js?v=' . $version;
        $this->context->smarty->assign('mollieAuthJsUrl', $jsUrl);

        // Add the shared CSS file
        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/globals.css?v=' . $version,
            'all',
            null,
            false
        );

        // Add AJAX URL with proper token for React app
        Media::addJsDef([
            'mollieAuthAjaxUrl' => addslashes($this->context->link->getAdminLink('AdminMollieAuthentication')),
        ]);

        // Add translations for React app
        Media::addJsDef([
            'mollieAuthTranslations' => [
                'mode' => addslashes($this->module->l('Mode', self::FILE_NAME)),
                'modeDescription' => addslashes($this->module->l('Choose operational mode for API.', self::FILE_NAME)),
                'live' => addslashes($this->module->l('Live', self::FILE_NAME)),
                'test' => addslashes($this->module->l('Test', self::FILE_NAME)),
                'testApiKey' => addslashes($this->module->l('Test API Key', self::FILE_NAME)),
                'liveApiKey' => addslashes($this->module->l('Live API Key', self::FILE_NAME)),
                'apiKeyPlaceholder' => addslashes($this->module->l('Enter your API key here', self::FILE_NAME)),
                'apiKeyDescription' => addslashes($this->module->l('Required for connecting to the %s mode.', self::FILE_NAME)),
                'connect' => addslashes($this->module->l('Connect', self::FILE_NAME)),
                'connecting' => addslashes($this->module->l('Connecting...', self::FILE_NAME)),
                'connected' => addslashes($this->module->l('Connected', self::FILE_NAME)),
                'connectedSuccessfully' => addslashes($this->module->l('Connected successfully!', self::FILE_NAME)),
                'show' => addslashes($this->module->l('Show', self::FILE_NAME)),
                'hide' => addslashes($this->module->l('Hide', self::FILE_NAME)),
                'whereApiKey' => addslashes($this->module->l('Where can I find my API key?', self::FILE_NAME)),
                'needHelp' => addslashes($this->module->l('Need Help?', self::FILE_NAME)),
                'getStarted' => addslashes($this->module->l('Get started', self::FILE_NAME)),
                'mollieDocumentation' => addslashes($this->module->l('Mollie documentation', self::FILE_NAME)),
                'paymentsQuestions' => addslashes($this->module->l('Payments related questions', self::FILE_NAME)),
                'contactMollieSupport' => addslashes($this->module->l('Contact Mollie Support', self::FILE_NAME)),
                'integrationQuestions' => addslashes($this->module->l('Integration questions', self::FILE_NAME)),
                'contactModuleDeveloper' => addslashes($this->module->l('Contact module developer', self::FILE_NAME)),
                'newToMollie' => addslashes($this->module->l('New to Mollie?', self::FILE_NAME)),
                'createAccount' => addslashes($this->module->l('Create a Mollie account', self::FILE_NAME)),
                'apiConfiguration' => addslashes($this->module->l('API Configuration', self::FILE_NAME)),
                'selectModeDescription' => addslashes($this->module->l('Select your operational mode and input API keys below.', self::FILE_NAME)),
                'connectionFailed' => addslashes($this->module->l('Connection failed. Please check your API key.', self::FILE_NAME)),
                'failedToLoadSettings' => addslashes($this->module->l('Failed to load current settings', self::FILE_NAME)),
                'failedToSwitchEnvironment' => addslashes($this->module->l('Failed to switch environment', self::FILE_NAME)),
                'switchEnvironment' => addslashes($this->module->l('Switch Environment', self::FILE_NAME)),
                'confirmSwitchEnvironment' => addslashes($this->module->l('Are you sure you want to switch to %s environment?', self::FILE_NAME)),
                'cancel' => addslashes($this->module->l('Cancel', self::FILE_NAME)),
                'switchTo' => addslashes($this->module->l('Switch to %s', self::FILE_NAME)),
            ],
        ]);
        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/authentication/authentication.tpl'
        );
    }

    /**
     * Handle AJAX requests
     */
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

    /**
     * Test API keys - copied from AdminMollieAjaxController::testApiKeys()
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    private function ajaxTestApiKeys(): void
    {
        $testKey = $this->tools->getValue('testKey');
        $liveKey = $this->tools->getValue('liveKey');

        /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
        $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);
        $apiTestFeedbackBuilder->setTestKey($testKey);
        $apiTestFeedbackBuilder->setLiveKey($liveKey);
        $apiKeysTestInfo = $apiTestFeedbackBuilder->buildParams();

        $this->context->smarty->assign($apiKeysTestInfo);
        // Return structured data instead of HTML template (api_test_results.tpl was removed)
        $this->ajaxRender(json_encode([
            'success' => true,
            'data' => $apiKeysTestInfo,
        ]));
    }

    /**
     * Get current API key settings
     */
    private function ajaxGetCurrentSettings(): void
    {
        try {
            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);
            $environment = $this->configuration->get(Config::MOLLIE_ENVIRONMENT);

            // Check if current API keys are valid using ApiTestFeedbackBuilder
            /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
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

            // Determine if connected based on current environment
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

    /**
     * Save API key to configuration
     */
    private function ajaxSaveApiKey(): void
    {
        try {
            $apiKey = $this->tools->getValue('api_key');
            $environment = $this->tools->getValue('environment'); // 'test' or 'live'

            if (!$apiKey || !$environment) {
                throw new MollieException($this->module->l('Missing required parameters', self::FILE_NAME));
            }

            // Validate API key using ApiTestFeedbackBuilder
            /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
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

            // Determine configuration key
            $configKey = ($environment === 'live') ? Config::MOLLIE_API_KEY : Config::MOLLIE_API_KEY_TEST;

            // Save to configuration
            $this->configuration->updateValue($configKey, $apiKey);

            // Also update environment setting
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

    /**
     * Switch environment between test and live
     */
    private function ajaxSwitchEnvironment(): void
    {
        try {
            $environment = $this->tools->getValue('environment'); // 'test' or 'live'

            if (!$environment || !in_array($environment, ['test', 'live'])) {
                throw new MollieException($this->module->l('Invalid environment parameter. Must be "test" or "live"', self::FILE_NAME));
            }

            // Convert to configuration value
            $environmentValue = ($environment === 'live') ? Config::ENVIRONMENT_LIVE : Config::ENVIRONMENT_TEST;

            // Update environment setting
            $this->configuration->updateValue(Config::MOLLIE_ENVIRONMENT, $environmentValue);

            // Get the current API keys to determine connection status
            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);

            // Check if the switched environment has a valid API key
            /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
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
