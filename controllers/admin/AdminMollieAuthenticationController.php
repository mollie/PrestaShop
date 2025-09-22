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

use Mollie\Config\Config;
use Mollie\Builder\ApiTestFeedbackBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMollieAuthenticationController extends ModuleAdminController
{
    const FILE_NAME = 'AdminMollieAuthenticationController';

    /** @var Mollie */
    public $module;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->context = Context::getContext();
    }

    /**
     * Initialize the authentication page
     */
    public function init()
    {
        parent::init();

        $version = time();

        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/admin/library/dist/assets/authorization.js?v=' . $version);
        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/authorization.css?v=' . $version,
            'all',
            null,
            false
        );

        // Add AJAX URL with proper token for React app
        Media::addJsDef([
            'mollieAuthAjaxUrl' => addslashes($this->context->link->getAdminLink('AdminMollieAuthentication')),
        ]);
        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/authentication/authentication.tpl'
        );
    }

    /**
     * Handle AJAX requests
     */
    public function displayAjax()
    {
        if (!Tools::isSubmit('ajax')) {
            return;
        }

        $action = Tools::getValue('action');

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
                    'message' => 'Invalid action'
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
    private function ajaxTestApiKeys()
    {
        $testKey = Tools::getValue('testKey');
        $liveKey = Tools::getValue('liveKey');

        /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
        $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);
        $apiTestFeedbackBuilder->setTestKey($testKey);
        $apiTestFeedbackBuilder->setLiveKey($liveKey);
        $apiKeysTestInfo = $apiTestFeedbackBuilder->buildParams();

        $this->context->smarty->assign($apiKeysTestInfo);
        $this->ajaxRender(json_encode([
            'template' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/api_test_results.tpl'),
        ]));
    }

    /**
     * Get current API key settings
     */
    private function ajaxGetCurrentSettings()
    {
        try {
            $testApiKey = Configuration::get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = Configuration::get(Config::MOLLIE_API_KEY);
            $environment = Configuration::get(Config::MOLLIE_ENVIRONMENT);

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
                    'live_key_valid' => $liveKeyValid
                ]
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => 'Failed to load settings',
                'error' => $e->getMessage()
            ]));
        }
    }

    /**
     * Save API key to configuration
     */
    private function ajaxSaveApiKey()
    {
        try {
            $apiKey = Tools::getValue('api_key');
            $environment = Tools::getValue('environment'); // 'test' or 'live'

            if (!$apiKey || !$environment) {
                throw new Exception('Missing required parameters');
            }

            // Validate API key using ApiTestFeedbackBuilder
            /** @var ApiTestFeedbackBuilder $apiTestFeedbackBuilder */
            $apiTestFeedbackBuilder = $this->module->getService(ApiTestFeedbackBuilder::class);

            $isTestKey = ($environment === 'test');
            $keyInfo = $apiTestFeedbackBuilder->getApiKeyInfo($apiKey, $isTestKey);

            if (!$keyInfo['status']) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => 'API key validation failed: Key does not exist or is invalid'
                ]));
                return;
            }

            if ($keyInfo['warning']) {
                $expectedPrefix = $isTestKey ? 'test_' : 'live_';
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => "API key validation failed: Key must start with '{$expectedPrefix}'"
                ]));
                return;
            }

            // Determine configuration key
            $configKey = ($environment === 'live') ? Config::MOLLIE_API_KEY : Config::MOLLIE_API_KEY_TEST;

            // Save to configuration
            Configuration::updateValue($configKey, $apiKey);

            // Also update environment setting
            $environmentValue = ($environment === 'live') ? Config::ENVIRONMENT_LIVE : Config::ENVIRONMENT_TEST;
            Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, $environmentValue);

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => 'API key saved successfully',
                'data' => [
                    'is_connected' => true,
                    'methods' => $keyInfo['methods'] ?? []
                ]
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => 'Failed to save API key',
                'error' => $e->getMessage()
            ]));
        }
    }

    /**
     * Switch environment between test and live
     */
    private function ajaxSwitchEnvironment()
    {
        try {
            $environment = Tools::getValue('environment'); // 'test' or 'live'

            if (!$environment || !in_array($environment, ['test', 'live'])) {
                throw new Exception('Invalid environment parameter. Must be "test" or "live"');
            }

            // Convert to configuration value
            $environmentValue = ($environment === 'live') ? Config::ENVIRONMENT_LIVE : Config::ENVIRONMENT_TEST;

            // Update environment setting
            Configuration::updateValue(Config::MOLLIE_ENVIRONMENT, $environmentValue);

            // Get the current API keys to determine connection status
            $testApiKey = Configuration::get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = Configuration::get(Config::MOLLIE_API_KEY);

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
                    'api_key' => $apiKey
                ]
            ]));
        } catch (Exception $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => 'Failed to switch environment',
                'error' => $e->getMessage()
            ]));
        }
    }
}
