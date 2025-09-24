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
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\CustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiService;
use Mollie\Service\CountryService;
use Mollie\Service\PaymentMethodService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMolliePaymentMethodsController extends ModuleAdminController
{
    const FILE_NAME = 'AdminMolliePaymentMethodsController';

    /** @var Mollie */
    public $module;

    /** @var ToolsAdapter */
    private $tools;

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var PaymentMethodService */
    private $paymentMethodService;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var ApiService */
    private $apiService;

    /** @var CountryService */
    private $countryService;

    /** @var CustomerRepository */
    private $customerRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->tools = $this->module->getService(ToolsAdapter::class);
        $this->configuration = $this->module->getService(ConfigurationAdapter::class);
        $this->paymentMethodService = $this->module->getService(PaymentMethodService::class);
        $this->paymentMethodRepository = $this->module->getService(PaymentMethodRepositoryInterface::class);
        $this->apiService = $this->module->getService(ApiService::class);
        $this->countryService = $this->module->getService(CountryService::class);
        $this->customerRepository = $this->module->getService(CustomerRepository::class);
        $this->logger = $this->module->getService(LoggerInterface::class);
    }

    /**
     * Initialize the payment methods page
     */
    public function init(): void
    {
        parent::init();

        //todo use module version after redesign will finish.
        $version = time();

        // Add the shared CSS file
        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/globals.css?v=' . $version,
            'all',
            null,
            false
        );

        // Pass URLs to template for ES module loading
        $jsUrl = $this->module->getPathUri() . 'views/js/admin/library/dist/assets/mollie-payment-methods.js?v=' . $version;
        $this->context->smarty->assign('molliePaymentMethodsJsUrl', $jsUrl);

        // Add AJAX URL with proper token for React app
        Media::addJsDef([
            'molliePaymentMethodsAjaxUrl' => addslashes($this->context->link->getAdminLink('AdminMolliePaymentMethods')),
        ]);

        // Add translations for React app
        Media::addJsDef([
            'molliePaymentMethodsTranslations' => [
                'paymentMethods' => addslashes($this->module->l('Payment Methods', self::FILE_NAME)),
                'configurePaymentMethods' => addslashes($this->module->l('Configure Payment Methods', self::FILE_NAME)),
                'enabled' => addslashes($this->module->l('Enabled', self::FILE_NAME)),
                'disabled' => addslashes($this->module->l('Disabled', self::FILE_NAME)),
                'methodName' => addslashes($this->module->l('Method Name', self::FILE_NAME)),
                'title' => addslashes($this->module->l('Title', self::FILE_NAME)),
                'description' => addslashes($this->module->l('Description', self::FILE_NAME)),
                'minAmount' => addslashes($this->module->l('Minimum Amount', self::FILE_NAME)),
                'maxAmount' => addslashes($this->module->l('Maximum Amount', self::FILE_NAME)),
                'surchargeFixed' => addslashes($this->module->l('Fixed Surcharge', self::FILE_NAME)),
                'surchargePercentage' => addslashes($this->module->l('Percentage Surcharge', self::FILE_NAME)),
                'surchargeLimit' => addslashes($this->module->l('Surcharge Limit', self::FILE_NAME)),
                'countries' => addslashes($this->module->l('Countries', self::FILE_NAME)),
                'excludedCountries' => addslashes($this->module->l('Excluded Countries', self::FILE_NAME)),
                'excludedCustomerGroups' => addslashes($this->module->l('Excluded Customer Groups', self::FILE_NAME)),
                'position' => addslashes($this->module->l('Position', self::FILE_NAME)),
                'save' => addslashes($this->module->l('Save', self::FILE_NAME)),
                'cancel' => addslashes($this->module->l('Cancel', self::FILE_NAME)),
                'refresh' => addslashes($this->module->l('Refresh Methods', self::FILE_NAME)),
                'saveSuccess' => addslashes($this->module->l('Configuration saved successfully', self::FILE_NAME)),
                'saveError' => addslashes($this->module->l('Failed to save configuration', self::FILE_NAME)),
                'refreshSuccess' => addslashes($this->module->l('Payment methods refreshed successfully', self::FILE_NAME)),
                'refreshError' => addslashes($this->module->l('Failed to refresh payment methods', self::FILE_NAME)),
                'loadingMethods' => addslashes($this->module->l('Loading payment methods...', self::FILE_NAME)),
                'noMethods' => addslashes($this->module->l('No payment methods available', self::FILE_NAME)),
                'apiNotConfigured' => addslashes($this->module->l('API not configured. Please configure API keys first.', self::FILE_NAME)),
                'confirmRefresh' => addslashes($this->module->l('This will sync payment methods with Mollie API. Continue?', self::FILE_NAME)),
                'environment' => addslashes($this->module->l('Environment', self::FILE_NAME)),
                'test' => addslashes($this->module->l('Test', self::FILE_NAME)),
                'live' => addslashes($this->module->l('Live', self::FILE_NAME)),
            ],
        ]);

        // Add configuration data for select options (same as FormBuilder)
        Media::addJsDef([
            'molliePaymentMethodsConfig' => [
                'countries' => $this->countryService->getActiveCountriesList(),
                'taxRulesGroups' => $this->getTaxRulesGroups(),
                'customerGroups' => $this->getCustomerGroups(),
                'onlyOrderMethods' => Config::ORDER_API_ONLY_METHODS,
                'onlyPaymentsMethods' => Config::PAYMENT_API_ONLY_METHODS,
            ],
        ]);

        $this->content = $this->context->smarty->fetch(
            $this->module->getLocalPath() . 'views/templates/admin/payment-methods/payment-methods.tpl'
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
            case 'getPaymentMethods':
                $this->ajaxGetPaymentMethods();
                break;
            case 'togglePaymentMethod':
                $this->ajaxTogglePaymentMethod();
                break;
            case 'updatePaymentMethod':
                $this->ajaxUpdatePaymentMethod();
                break;
            case 'savePaymentMethodSettings':
                $this->ajaxSavePaymentMethodSettings();
                break;
            case 'updateMethodsOrder':
                $this->ajaxUpdateMethodsOrder();
                break;
            case 'refreshMethods':
                $this->ajaxRefreshMethods();
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
     * Get all payment methods with their configuration
     */
    private function ajaxGetPaymentMethods(): void
    {
        try {
            // Check if API is configured - same logic as authentication page
            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);
            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);

            $currentApiKey = $environment ? $liveApiKey : $testApiKey;

            if (empty($currentApiKey)) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => $this->module->l('API not configured. Please configure API keys first.', self::FILE_NAME),
                ]));
                return;
            }

            $shopId = $this->context->shop->id;

            // Get available payment methods from Mollie API (same as FormBuilder does)
            $apiMethods = [];
            try {
                $mollieClient = $this->module->getApiClient();
                if ($mollieClient) {
                    $apiMethods = $this->apiService->getMethodsForConfig($mollieClient);
                }
            } catch (Exception $e) {
                $this->logger->error('Failed to fetch methods from Mollie API', [
                    'exception' => ExceptionUtility::getExceptions($e),
                ]);
            }

            // If no methods from API, return empty response
            if (empty($apiMethods)) {
                $this->ajaxRender(json_encode([
                    'success' => true,
                    'data' => [
                        'methods' => [],
                        'countries' => $this->countryService->getActiveCountriesList(),
                        'taxRulesGroups' => $this->getTaxRulesGroups(),
                        'customerGroups' => $this->getCustomerGroups(),
                        'onlyOrderMethods' => Config::ORDER_API_ONLY_METHODS,
                        'onlyPaymentsMethods' => Config::PAYMENT_API_ONLY_METHODS,
                        'environment' => $environment ? 'live' : 'test',
                        'is_connected' => false,
                    ],
                ]));
                return;
            }

            // Format API methods for the modern React frontend (same as FormBuilder does)
            $formattedMethods = [];
            foreach ($apiMethods as $method) {
                $methodId = $method['id'];
                $methodObj = $method['obj']; // This comes from getMethodsObjForConfig()

                // Convert FormBuilder API method structure to modern frontend format
                $formattedMethods[] = [
                    'id' => $methodId,
                    'name' => $method['name'],
                    'type' => $methodId === 'creditcard' ? 'card' : 'other',
                    'status' => $methodObj->enabled ? 'active' : 'inactive',
                    'isExpanded' => false, // Will be handled by frontend state
                    'position' => (int) ($methodObj->position ?? 0),
                    'settings' => [
                        'enabled' => (bool) $methodObj->enabled,
                        'title' => $method['name'],
                        'mollieComponents' => true, // Default
                        'oneClickPayments' => false, // Default
                        'transactionDescription' => $methodObj->description ?: 'Order %order_number%',
                        'apiSelection' => $methodObj->method === 'orders' ? 'orders' : 'payments',
                        'paymentRestrictions' => [
                            'acceptFrom' => $methodObj->is_countries_applicable ? 'specific' : 'all',
                            'excludeCountries' => $method['excludedCountries'] ?? [],
                            'excludeCustomerGroups' => $method['excludedCustomerGroups'] ?? [],
                        ],
                        'paymentFees' => [
                            'enabled' => (float) $methodObj->surcharge_fixed_amount_tax_excl > 0 || (float) $methodObj->surcharge_percentage > 0,
                            'type' => (float) $methodObj->surcharge_percentage > 0 ? 'percentage' : 'fixed',
                            'taxGroup' => 'b2c', // Default
                            'maxFee' => $methodObj->surcharge_limit ?? '0.00',
                            'minAmount' => $methodObj->surcharge_fixed_amount_tax_excl ?? '0.00',
                            'maxAmount' => $methodObj->surcharge_percentage ?? '0.00',
                        ],
                        'orderRestrictions' => [
                            'minAmount' => $method['minimumAmount'] ? $method['minimumAmount']['value'] : '0.00',
                            'maxAmount' => $method['maximumAmount'] ? $method['maximumAmount']['value'] : '0.00',
                        ],
                    ],
                ];
            }

            // Sort by position
            usort($formattedMethods, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            // Prepare data structure for modern React frontend
            $responseData = [
                'success' => true,
                'data' => [
                    'methods' => $formattedMethods, // Use 'methods' like original interface
                    'countries' => $this->countryService->getActiveCountriesList(),
                    'taxRulesGroups' => $this->getTaxRulesGroups(),
                    'customerGroups' => $this->getCustomerGroups(),
                    'onlyOrderMethods' => Config::ORDER_API_ONLY_METHODS,
                    'onlyPaymentsMethods' => Config::PAYMENT_API_ONLY_METHODS,
                    'environment' => $environment ? 'live' : 'test',
                    'is_connected' => !empty($formattedMethods),
                ],
            ];

            $this->ajaxRender(json_encode($responseData));
        } catch (Exception $e) {
            $this->logger->error('Failed to get payment methods', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to load payment methods', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Toggle payment method enabled/disabled status
     */
    private function ajaxTogglePaymentMethod(): void
    {
        try {
            $methodId = $this->tools->getValue('method_id');
            $enabled = (bool) $this->tools->getValue('enabled');

            if (!$methodId) {
                throw new MollieException($this->module->l('Missing method ID', self::FILE_NAME));
            }

            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
            $shopId = $this->context->shop->id;

            // Get the payment method by method ID
            $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

            if (!$paymentMethodId) {
                throw new MollieException($this->module->l('Payment method not found', self::FILE_NAME));
            }

            // Load and update the payment method
            $paymentMethod = new MolPaymentMethod((int) $paymentMethodId);
            $paymentMethod->enabled = $enabled;
            $result = $paymentMethod->save();

            if (!$result) {
                throw new MollieException($this->module->l('Failed to save payment method', self::FILE_NAME));
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('Payment method updated successfully', self::FILE_NAME),
            ]));
        } catch (MollieException $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->logger->error('Failed to toggle payment method', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to update payment method', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Update payment method configuration
     */
    private function ajaxUpdatePaymentMethod(): void
    {
        try {
            $methodId = $this->tools->getValue('method_id');
            $configuration = json_decode($this->tools->getValue('configuration'), true);

            if (!$methodId || !$configuration) {
                throw new MollieException($this->module->l('Missing required parameters', self::FILE_NAME));
            }

            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
            $shopId = $this->context->shop->id;

            // Get the payment method by method ID
            $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

            if (!$paymentMethodId) {
                throw new MollieException($this->module->l('Payment method not found', self::FILE_NAME));
            }

            // Load and update the payment method
            $paymentMethod = new MolPaymentMethod((int) $paymentMethodId);

            // Update basic settings
            if (isset($configuration['title'])) {
                $paymentMethod->method_name = $configuration['title'];
            }
            if (isset($configuration['description'])) {
                $paymentMethod->description = $configuration['description'];
            }
            if (isset($configuration['min_amount'])) {
                $paymentMethod->minimal_order_value = $configuration['min_amount'];
                $paymentMethod->min_amount = (float) $configuration['min_amount'];
            }
            if (isset($configuration['max_amount'])) {
                $paymentMethod->max_order_value = $configuration['max_amount'];
                $paymentMethod->max_amount = (float) $configuration['max_amount'];
            }
            if (isset($configuration['surcharge_fixed'])) {
                $paymentMethod->surcharge_fixed_amount_tax_excl = (float) $configuration['surcharge_fixed'];
            }
            if (isset($configuration['surcharge_percentage'])) {
                $paymentMethod->surcharge_percentage = (float) $configuration['surcharge_percentage'];
            }
            if (isset($configuration['surcharge_limit'])) {
                $paymentMethod->surcharge_limit = (float) $configuration['surcharge_limit'];
            }
            if (isset($configuration['custom_logo'])) {
                $paymentMethod->images_json = json_encode($configuration['custom_logo']);
            }

            $result = $paymentMethod->save();

            if (!$result) {
                throw new MollieException($this->module->l('Failed to save payment method configuration', self::FILE_NAME));
            }

            // TODO: Implement country and customer group restriction updates
            // For now, just skip these updates

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('Payment method configuration saved successfully', self::FILE_NAME),
            ]));
        } catch (MollieException $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->logger->error('Failed to update payment method configuration', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to save configuration', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Update payment methods order (drag-drop reordering)
     */
    private function ajaxUpdateMethodsOrder(): void
    {
        try {
            $methodIds = json_decode($this->tools->getValue('method_ids'), true);

            if (!is_array($methodIds)) {
                throw new MollieException($this->module->l('Invalid method IDs provided', self::FILE_NAME));
            }

            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
            $shopId = $this->context->shop->id;

            // Update each method's position
            $updatedCount = 0;
            foreach ($methodIds as $position => $methodId) {
                $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

                if ($paymentMethodId) {
                    $paymentMethod = new MolPaymentMethod((int) $paymentMethodId);
                    $paymentMethod->position = $position + 1; // Position starts from 1
                    if ($paymentMethod->save()) {
                        $updatedCount++;
                    }
                }
            }

            if ($updatedCount === 0) {
                throw new MollieException($this->module->l('No payment methods were updated', self::FILE_NAME));
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('Payment methods order updated successfully', self::FILE_NAME),
                'data' => [
                    'updated' => $updatedCount,
                ],
            ]));
        } catch (MollieException $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->logger->error('Failed to update payment methods order', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to update order', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Refresh payment methods from Mollie API
     */
    private function ajaxRefreshMethods(): void
    {
        try {
            // Check if API is configured - same logic as authentication page
            $testApiKey = $this->configuration->get(Config::MOLLIE_API_KEY_TEST);
            $liveApiKey = $this->configuration->get(Config::MOLLIE_API_KEY);
            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);

            $currentApiKey = $environment ? $liveApiKey : $testApiKey;

            if (empty($currentApiKey)) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => $this->module->l('API not configured. Please configure API keys first.', self::FILE_NAME),
                ]));
                return;
            }

            $shopId = $this->context->shop->id;

            // Get current payment methods from database
            $currentMethods = $this->paymentMethodRepository->getMethodsForCheckout($environment, $shopId) ?: [];
            $currentMethodIds = array_column($currentMethods, 'id_method');

            // Get methods from Mollie API and save them
            $mollieClient = $this->module->getApiClient();
            $apiMethods = $this->apiService->getMethodsForConfig($mollieClient);

            $savedMethodIds = [];
            $newCount = 0;
            $updatedCount = 0;

            foreach ($apiMethods as $method) {
                $methodId = $method['id'];
                $isNew = !in_array($methodId, $currentMethodIds);

                try {
                    $paymentMethod = $this->paymentMethodService->savePaymentMethod($method);
                    $savedMethodIds[] = $paymentMethod->id_method;

                    if ($isNew) {
                        $newCount++;
                    } else {
                        $updatedCount++;
                    }
                } catch (Exception $e) {
                    $this->logger->error('Failed to save payment method during refresh', [
                        'method_id' => $methodId,
                        'exception' => ExceptionUtility::getExceptions($e),
                    ]);
                    continue;
                }
            }

            // Remove old payment methods that are no longer available
            $removedCount = 0;
            $methodsToRemove = array_diff($currentMethodIds, $savedMethodIds);
            if (!empty($methodsToRemove)) {
                $removedCount = count($methodsToRemove);
                $this->paymentMethodRepository->deleteOldPaymentMethods($savedMethodIds, $environment, $shopId);
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('Payment methods refreshed successfully', self::FILE_NAME),
                'data' => [
                    'updated' => $updatedCount,
                    'new' => $newCount,
                    'removed' => $removedCount,
                    'total' => count($savedMethodIds),
                ],
            ]));
        } catch (Exception $e) {
            $this->logger->error('Failed to refresh payment methods', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to refresh payment methods', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Save payment method settings (new individual save functionality)
     */
    private function ajaxSavePaymentMethodSettings(): void
    {
        try {
            $methodId = $this->tools->getValue('method_id');
            $settingsJson = $this->tools->getValue('settings');

            if (!$methodId || !$settingsJson) {
                throw new MollieException($this->module->l('Missing required parameters', self::FILE_NAME));
            }

            $settings = json_decode($settingsJson, true);
            if (!$settings) {
                throw new MollieException($this->module->l('Invalid settings format', self::FILE_NAME));
            }

            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);

            // Use PaymentMethodService to save the settings (same as form submission)
            $result = $this->paymentMethodService->savePaymentMethod([
                'method_id' => $methodId,
                'enabled' => $settings['enabled'] ?? false,
                'title' => $settings['title'] ?? '',
                'method_description' => $settings['transactionDescription'] ?? '',
                'use_custom_logo' => false, // Not implemented in current UI
                'min_amount' => $settings['orderRestrictions']['minAmount'] ?? '',
                'max_amount' => $settings['orderRestrictions']['maxAmount'] ?? '',
                'surcharge_fixed_amount_tax_excl' => $settings['paymentFees']['maxFee'] ?? '',
                'surcharge_percentage' => '',
                'surcharge_limit' => '',
                'countries[]' => [], // TODO: implement country restrictions
                'customer_groups_excluded[]' => [], // TODO: implement customer group restrictions
                'payment_api' => $settings['apiSelection'] ?? 'payments',
                'mollie_components' => $settings['mollieComponents'] ?? false,
                'single_click_payment' => $settings['oneClickPayments'] ?? false,
            ], $environment);

            if ($result) {
                $this->ajaxRender(json_encode([
                    'success' => true,
                    'message' => $this->module->l('Payment method settings saved successfully', self::FILE_NAME),
                ]));
            } else {
                throw new MollieException($this->module->l('Failed to save payment method settings', self::FILE_NAME));
            }
        } catch (MollieException $e) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        } catch (Exception $e) {
            $this->logger->error('Failed to save payment method settings', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to save payment method settings', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Get tax rules groups for select options
     */
    private function getTaxRulesGroups(): array
    {
        $taxRulesGroups = [];

        try {
            // Use the TaxRulesGroupRepositoryInterface if available
            if (method_exists($this, 'taxRulesGroupRepository')) {
                $groups = $this->taxRulesGroupRepository->getTaxRulesGroups($this->context->shop->id);
            } else {
                // Fallback to direct database query
                $sql = 'SELECT id_tax_rules_group, name FROM ' . _DB_PREFIX_ . 'tax_rules_group WHERE active = 1 AND deleted = 0';
                $groups = Db::getInstance()->executeS($sql) ?: [];
            }

            foreach ($groups as $group) {
                $taxRulesGroups[] = [
                    'value' => $group['id_tax_rules_group'],
                    'label' => $group['name'],
                ];
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to get tax rules groups', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }

        return $taxRulesGroups;
    }

    /**
     * Get customer groups for select options
     */
    private function getCustomerGroups(): array
    {
        $customerGroups = [];

        try {
            $groups = Group::getGroups($this->context->language->id);

            foreach ($groups as $group) {
                $customerGroups[] = [
                    'value' => $group['id_group'],
                    'label' => $group['name'],
                ];
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to get customer groups', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }

        return $customerGroups;
    }
}