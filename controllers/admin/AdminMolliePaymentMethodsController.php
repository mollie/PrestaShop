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
use Mollie\Repository\CountryRepository;
use Mollie\Repository\CustomerRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;
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

    /** @var PaymentMethodLangRepositoryInterface */
    private $paymentMethodLangRepository;

    /** @var ApiService */
    private $apiService;

    /** @var CountryService */
    private $countryService;

    /** @var CountryRepository */
    private $countryRepository;

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
        $this->paymentMethodLangRepository = $this->module->getService(PaymentMethodLangRepositoryInterface::class);
        $this->apiService = $this->module->getService(ApiService::class);
        $this->countryService = $this->module->getService(CountryService::class);
        $this->countryRepository = $this->module->getService(CountryRepository::class);
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
                // Main page
                'paymentMethods' => addslashes($this->module->l('Payment Methods', self::FILE_NAME)),
                'configurePaymentMethods' => addslashes($this->module->l('Configure Payment Methods', self::FILE_NAME)),
                'enabled' => addslashes($this->module->l('Enabled', self::FILE_NAME)),
                'disabled' => addslashes($this->module->l('Disabled', self::FILE_NAME)),
                'enabledPaymentMethods' => addslashes($this->module->l('Enabled payment methods', self::FILE_NAME)),
                'disabledPaymentMethods' => addslashes($this->module->l('Disabled payment methods', self::FILE_NAME)),

                // Payment method card
                'showSettings' => addslashes($this->module->l('Show settings', self::FILE_NAME)),
                'hideSettings' => addslashes($this->module->l('Hide settings', self::FILE_NAME)),
                'active' => addslashes($this->module->l('Active', self::FILE_NAME)),
                'inactive' => addslashes($this->module->l('Inactive', self::FILE_NAME)),

                // Basic settings
                'basicSettings' => addslashes($this->module->l('Basic settings', self::FILE_NAME)),
                'activateDeactivate' => addslashes($this->module->l('Activate/Deactivate', self::FILE_NAME)),
                'enablePaymentMethod' => addslashes($this->module->l('Enable payment method', self::FILE_NAME)),
                'useEmbeddedCreditCardForm' => addslashes($this->module->l('Use embedded credit card form in the checkout', self::FILE_NAME)),
                'enableMollieComponents' => addslashes($this->module->l('Enable Mollie Components', self::FILE_NAME)),
                'letCustomerSaveCreditCard' => addslashes($this->module->l('Let customer save their credit card data for future orders', self::FILE_NAME)),
                'useOneClickPayments' => addslashes($this->module->l('Use one-click payments', self::FILE_NAME)),
                'paymentTitle' => addslashes($this->module->l('Payment Title', self::FILE_NAME)),
                'paymentTitlePlaceholder' => addslashes($this->module->l('Payment Method #1', self::FILE_NAME)),

                // API Selection
                'apiSelection' => addslashes($this->module->l('API Selection', self::FILE_NAME)),
                'payments' => addslashes($this->module->l('Payments', self::FILE_NAME)),
                'orders' => addslashes($this->module->l('Orders', self::FILE_NAME)),
                'transactionDescription' => addslashes($this->module->l('Transaction Description', self::FILE_NAME)),
                'transactionDescriptionPlaceholder' => addslashes($this->module->l('Enter transaction description', self::FILE_NAME)),
                'readMore' => addslashes($this->module->l('Read more', self::FILE_NAME)),
                'aboutDifferences' => addslashes($this->module->l('about the differences between Payments and Orders API', self::FILE_NAME)),

                // Custom Logo
                'useCustomLogo' => addslashes($this->module->l('Use custom logo', self::FILE_NAME)),
                'uploadLogo' => addslashes($this->module->l('Upload Logo', self::FILE_NAME)),
                'replaceLogo' => addslashes($this->module->l('Replace Logo', self::FILE_NAME)),
                'logoUploadHelp' => addslashes($this->module->l('Upload a JPG or PNG image. Maximum dimensions: 256x64 pixels. Maximum file size: 2MB.', self::FILE_NAME)),

                // Apple Pay Settings
                'applePayDirectSettings' => addslashes($this->module->l('Apple Pay Direct settings', self::FILE_NAME)),
                'applePayDirectProductPage' => addslashes($this->module->l('Apple Pay Direct product page', self::FILE_NAME)),
                'enableApplePayProductPages' => addslashes($this->module->l('Enable Apple Pay on product pages', self::FILE_NAME)),
                'applePayDirectCartPage' => addslashes($this->module->l('Apple Pay Direct cart page', self::FILE_NAME)),
                'enableApplePayCartPages' => addslashes($this->module->l('Enable Apple Pay on cart pages', self::FILE_NAME)),
                'applePayDirectButtonStyle' => addslashes($this->module->l('Apple Pay Direct button style', self::FILE_NAME)),
                'applePayButtonBlack' => addslashes($this->module->l('Black', self::FILE_NAME)),
                'applePayButtonOutline' => addslashes($this->module->l('Outline', self::FILE_NAME)),
                'applePayButtonWhite' => addslashes($this->module->l('White', self::FILE_NAME)),

                // Payment Restrictions
                'paymentRestrictions' => addslashes($this->module->l('Payment restrictions', self::FILE_NAME)),
                'acceptPaymentsFrom' => addslashes($this->module->l('Accept payments from', self::FILE_NAME)),
                'allCountries' => addslashes($this->module->l('All countries', self::FILE_NAME)),
                'selectedCountries' => addslashes($this->module->l('Selected countries', self::FILE_NAME)),
                'acceptPaymentsFromSpecificCountries' => addslashes($this->module->l('Accept payments from specific countries', self::FILE_NAME)),
                'selectCountriesAccept' => addslashes($this->module->l('Select countries to accept payments from', self::FILE_NAME)),
                'excludePaymentsFromCountries' => addslashes($this->module->l('Exclude payments from specific countries', self::FILE_NAME)),
                'selectCountriesToExclude' => addslashes($this->module->l('Select countries to exclude', self::FILE_NAME)),
                'excludeCustomerGroups' => addslashes($this->module->l('Restrict to customer groups', self::FILE_NAME)),
                'selectCustomerGroups' => addslashes($this->module->l('Select customer groups that will not see this payment method', self::FILE_NAME)),
                'customerGroupsHelp' => addslashes($this->module->l('If no groups are selected, the payment method will be available to all customers.', self::FILE_NAME)),
                'guest' => addslashes($this->module->l('Guest', self::FILE_NAME)),
                'customerGroup' => addslashes($this->module->l('Customer Group', self::FILE_NAME)),

                // Payment Fees
                'paymentFees' => addslashes($this->module->l('Payment fees', self::FILE_NAME)),
                'enablePaymentFee' => addslashes($this->module->l('Enable payment fee', self::FILE_NAME)),
                'paymentFeeType' => addslashes($this->module->l('Payment fee type', self::FILE_NAME)),
                'fixedFee' => addslashes($this->module->l('Fixed fee', self::FILE_NAME)),
                'fixedFeeTaxIncl' => addslashes($this->module->l('Fixed fee (tax incl)', self::FILE_NAME)),
                'fixedFeeTaxExcl' => addslashes($this->module->l('Fixed fee (tax excl)', self::FILE_NAME)),
                'percentageFee' => addslashes($this->module->l('Percentage', self::FILE_NAME)),
                'percentageFeeLabel' => addslashes($this->module->l('Percentage fee', self::FILE_NAME)),
                'combinedFee' => addslashes($this->module->l('Combined payment surcharge limit', self::FILE_NAME)),
                'noFee' => addslashes($this->module->l('No fee', self::FILE_NAME)),
                'paymentFeeTaxGroup' => addslashes($this->module->l('Payment fee tax group', self::FILE_NAME)),
                'taxRulesGroupForFixedFee' => addslashes($this->module->l('Tax rules group for fixed fee', self::FILE_NAME)),
                'maximumFee' => addslashes($this->module->l('Maximum fee', self::FILE_NAME)),
                'minimumAmount' => addslashes($this->module->l('Minimum amount', self::FILE_NAME)),
                'maximumAmount' => addslashes($this->module->l('Maximum amount', self::FILE_NAME)),
                'paymentFeeEmailHelp' => addslashes($this->module->l('Add "(payment_fee)" in email translations to display it in your email template.', self::FILE_NAME)),

                // Order Restrictions
                'orderRestrictions' => addslashes($this->module->l('Order restrictions', self::FILE_NAME)),

                // Actions
                'save' => addslashes($this->module->l('Save', self::FILE_NAME)),
                'saving' => addslashes($this->module->l('Saving...', self::FILE_NAME)),
                'loadingMethods' => addslashes($this->module->l('Loading payment methods...', self::FILE_NAME)),
                'loadingError' => addslashes($this->module->l('Failed to load payment methods', self::FILE_NAME)),
                'saveSettings' => addslashes($this->module->l('Save Settings', self::FILE_NAME)),

                // Transaction Description Help
                'transactionDescriptionHelp' => addslashes($this->module->l('Use any of the following variables to create a transaction description for payments that use this method:', self::FILE_NAME)),
                'transactionDescriptionVariables' => addslashes($this->module->l('{orderNumber}, {storeName}, {countryCode}, {cart.id}, {order.reference}, {customer.firstname}, {customer.lastname}, {customer.company}', self::FILE_NAME)),

                // Messages
                'paymentMethodNotFound' => addslashes($this->module->l('Payment method not found', self::FILE_NAME)),
                'settingsSavedSuccessfully' => addslashes($this->module->l('Settings saved successfully!', self::FILE_NAME)),
                'failedToSaveSettings' => addslashes($this->module->l('Failed to save settings', self::FILE_NAME)),
                'paymentMethodsOrderUpdated' => addslashes($this->module->l('Payment methods order updated successfully!', self::FILE_NAME)),
                'failedToUpdateOrder' => addslashes($this->module->l('Failed to update payment methods order', self::FILE_NAME)),
                'savingNewOrder' => addslashes($this->module->l('Saving new order...', self::FILE_NAME)),
                'noPaymentMethods' => addslashes($this->module->l('No payment methods', self::FILE_NAME)),
                'paymentMethodsWillAppear' => addslashes($this->module->l('Payment methods will appear here once configured', self::FILE_NAME)),

                // Custom Logo Upload
                'pleaseUploadJpgOrPng' => addslashes($this->module->l('Please upload a JPG or PNG file', self::FILE_NAME)),
                'fileSizeTooLarge' => addslashes($this->module->l('File size must be less than 2MB', self::FILE_NAME)),
                'imageDimensionsTooLarge' => addslashes($this->module->l('Image dimensions must be maximum 256x64 pixels', self::FILE_NAME)),
                'failedToUploadLogo' => addslashes($this->module->l('Failed to upload logo. Please try again.', self::FILE_NAME)),
                'invalidImageFile' => addslashes($this->module->l('Invalid image file', self::FILE_NAME)),
                'uploading' => addslashes($this->module->l('Uploading...', self::FILE_NAME)),
                'customLogoPreview' => addslashes($this->module->l('Custom logo preview', self::FILE_NAME)),
                'logoUploadedSuccessfully' => addslashes($this->module->l('Logo uploaded successfully!', self::FILE_NAME)),
                'customLogo' => addslashes($this->module->l('Custom Logo', self::FILE_NAME)),
                'remove' => addslashes($this->module->l('Remove', self::FILE_NAME)),

                // Apple Pay Button Descriptions
                'applePayButtonBlackDesc' => addslashes($this->module->l('Black Apple Pay button', self::FILE_NAME)),
                'applePayButtonOutlineDesc' => addslashes($this->module->l('White with outline', self::FILE_NAME)),
                'applePayButtonWhiteDesc' => addslashes($this->module->l('White Apple Pay button', self::FILE_NAME)),

                // Select Placeholders
                'selectOption' => addslashes($this->module->l('Select option', self::FILE_NAME)),
                'selectOptions' => addslashes($this->module->l('Select options', self::FILE_NAME)),
                'itemsSelected' => addslashes($this->module->l('%s selected', self::FILE_NAME)),

                // Drag and drop
                'dragPaymentOptionsToReorder' => addslashes($this->module->l('Drag payment options to reorder', self::FILE_NAME)),
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
            error_log('displayAjax: No ajax submit');
            return;
        }

        $action = $this->tools->getValue('action');
        error_log('displayAjax: Received action = ' . $action);

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
            case 'uploadCustomLogo':
                $this->ajaxUploadCustomLogo();
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
                $this->logger->info('No API methods found from Mollie API');
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

            $this->logger->info('Found ' . count($apiMethods) . ' API methods from Mollie');

            // Format API methods for the modern React frontend (same as FormBuilder does)
            $formattedMethods = [];
            foreach ($apiMethods as $method) {
                try {
                    $methodId = $method['id'];
                    $methodObj = $method['obj']; // This comes from getMethodsObjForConfig()

                    // Validate method object
                    if (!$methodObj) {
                        $this->logger->warning('Method object is null for method: ' . $methodId);
                        continue;
                    }

                    // Convert FormBuilder API method structure to modern frontend format
                    $formattedMethods[] = [
                        'id' => $methodId,
                        'name' => $method['name'],
                        'type' => $methodId === 'creditcard' ? 'card' : 'other',
                        'status' => (isset($methodObj->enabled) && $methodObj->enabled) ? 'active' : 'inactive',
                        'isExpanded' => false, // Will be handled by frontend state
                        'position' => (int) (isset($methodObj->position) ? $methodObj->position : 0),
                        'image' => $method['image'] ?? null,
                        'settings' => [
                            'enabled' => (bool) (isset($methodObj->enabled) ? $methodObj->enabled : false),
                            'title' => $this->getPaymentMethodTitle($methodId, $method['name'] ?? ''),
                            'mollieComponents' => true, // Default
                            'oneClickPayments' => false, // Default
                            'transactionDescription' => (isset($methodObj->description) && $methodObj->description) ? $methodObj->description : '{orderNumber}',
                            'apiSelection' => (isset($methodObj->method) && $methodObj->method === 'orders') ? 'orders' : 'payments',
                            'useCustomLogo' => $methodId === 'creditcard' ? (bool) ($this->configuration->get(\Mollie\Config\Config::MOLLIE_SHOW_CUSTOM_LOGO) ?: 0) : false,
                            'customLogoUrl' => $methodId === 'creditcard' ? $this->getCustomLogoUrl() : null,
                            'paymentRestrictions' => [
                                'acceptFrom' => (isset($methodObj->is_countries_applicable) && $methodObj->is_countries_applicable) ? 'selected' : 'all',
                                'selectedCountries' => $method['countries'] ?? [], // For when acceptFrom = 'selected'
                                'excludeCountries' => $method['excludedCountries'] ?? [],
                                'excludeCustomerGroups' => $method['excludedCustomerGroups'] ?? [],
                            ],
                            'paymentFees' => [
                                'enabled' => (int) (isset($methodObj->surcharge) ? $methodObj->surcharge : 0) > 0,
                                'type' => $this->getPaymentFeeType($methodObj),
                                'taxGroup' => isset($methodObj->tax_rules_group_id) ? (string)$methodObj->tax_rules_group_id : '0',
                                // Fixed fee fields - fixedFeeTaxIncl must be calculated as it's not stored in DB
                                'fixedFeeTaxIncl' => $this->calculateFixedFeeTaxIncl($methodObj),
                                'fixedFeeTaxExcl' => isset($methodObj->surcharge_fixed_amount_tax_excl) ? $methodObj->surcharge_fixed_amount_tax_excl : '0.00',
                                // Percentage fee fields
                                'percentageFee' => isset($methodObj->surcharge_percentage) ? $methodObj->surcharge_percentage : '0.00',
                                'maxFeeCap' => isset($methodObj->surcharge_limit) ? $methodObj->surcharge_limit : '0.00',
                            ],
                            'orderRestrictions' => [
                                // Load from DB if set, otherwise fallback to Mollie API defaults
                                'minAmount' => (isset($methodObj->min_amount) && $methodObj->min_amount > 0)
                                    ? $methodObj->min_amount
                                    : ($method['minimumAmount'] ? $method['minimumAmount']['value'] : '0.00'),
                                'maxAmount' => (isset($methodObj->max_amount) && $methodObj->max_amount > 0)
                                    ? $methodObj->max_amount
                                    : ($method['maximumAmount'] ? $method['maximumAmount']['value'] : '0.00'),
                            ],
                            'applePaySettings' => $methodId === 'applepay' ? [
                                'directProduct' => (bool) ($this->configuration->get(\Mollie\Config\Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT) ?: 0),
                                'directCart' => (bool) ($this->configuration->get(\Mollie\Config\Config::MOLLIE_APPLE_PAY_DIRECT_CART) ?: 0),
                                'buttonStyle' => (int) ($this->configuration->get(\Mollie\Config\Config::MOLLIE_APPLE_PAY_DIRECT_STYLE) ?: 0),
                            ] : null,
                        ],
                    ];
                } catch (Exception $e) {
                    $this->logger->error('Error formatting payment method: ' . $methodId, [
                        'exception' => ExceptionUtility::getExceptions($e),
                        'method_data' => $method,
                    ]);
                    // Skip this method and continue with others
                    continue;
                }
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

            // Final validation - ensure we have a valid response structure
            if (!isset($responseData['success']) || !isset($responseData['data'])) {
                $this->logger->error('Invalid response data structure', ['responseData' => $responseData]);
                $responseData = [
                    'success' => false,
                    'message' => 'Invalid response structure',
                ];
            }

            $this->logger->info('Sending payment methods response', [
                'methods_count' => isset($responseData['data']['methods']) ? count($responseData['data']['methods']) : 0,
                'success' => $responseData['success'],
            ]);

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
                        ++$updatedCount;
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
                        ++$newCount;
                    } else {
                        ++$updatedCount;
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
     * Get payment method title from translations table or fallback to API name
     */
    private function getPaymentMethodTitle(string $methodId, string $defaultName): string
    {
        try {
            $langId = (int)$this->context->language->id;
            $shopId = $this->context->shop->id;

            // Try to get custom title from translations table
            $translation = $this->paymentMethodLangRepository->findOneBy([
                'id_method' => $methodId,
                'id_lang' => $langId,
                'id_shop' => $shopId,
            ]);

            // The field is called 'text' not 'name' in MolPaymentMethodTranslations entity
            if ($translation && isset($translation->text) && !empty($translation->text)) {
                return $translation->text;
            }
        } catch (\Exception $e) {
            error_log('Error getting payment method title: ' . $e->getMessage());
        }

        // Fallback to API method name
        return $defaultName;
    }

    /**
     * Save payment method settings (new individual save functionality)
     */
    private function ajaxSavePaymentMethodSettings(): void
    {
        // Log at the very start to confirm function is called
        error_log('=== ajaxSavePaymentMethodSettings called ===');

        try {
            $methodId = $this->tools->getValue('method_id');
            $settingsJson = $this->tools->getValue('settings');

            error_log('Received method_id: ' . $methodId);
            error_log('Received settings JSON: ' . $settingsJson);

            if (!$methodId || !$settingsJson) {
                error_log('ERROR: Missing required parameters');
                throw new MollieException($this->module->l('Missing required parameters', self::FILE_NAME));
            }

            $settings = json_decode($settingsJson, true);
            if (!$settings) {
                throw new MollieException($this->module->l('Invalid settings format', self::FILE_NAME));
            }

            // Debug logging
            $this->logger->info('Saving payment method settings', [
                'method_id' => $methodId,
                'title' => $settings['title'] ?? 'NOT SET',
                'transactionDescription' => $settings['transactionDescription'] ?? 'NOT SET',
                'full_settings' => $settings,
            ]);

            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
            $shopId = $this->context->shop->id;

            // Get payment method ID from database (or create new if doesn't exist)
            $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

            // Build form data structure that matches old controller format
            $formData = [
                'id' => $methodId,
                'enabled' => $settings['enabled'] ?? false,
                'method_name' => $settings['title'] ?? '',
                'description' => $settings['transactionDescription'] ?? '',
                'method' => $settings['apiSelection'] ?? 'payments',
                'min_amount' => $settings['orderRestrictions']['minAmount'] ?? '',
                'max_amount' => $settings['orderRestrictions']['maxAmount'] ?? '',
            ];

            // Debug: Log what we're about to save
            $this->logger->info('Form data prepared', [
                'method_name' => $formData['method_name'],
                'description' => $formData['description'],
            ]);

            // Load existing payment method or create new one
            $paymentMethod = new \MolPaymentMethod();
            if ($paymentMethodId) {
                $paymentMethod = new \MolPaymentMethod((int) $paymentMethodId);
            }

            // Update basic fields
            $paymentMethod->id_method = $methodId;
            $paymentMethod->method_name = $methodId; // Keep API method ID as method_name (not the custom title)
            $paymentMethod->enabled = $formData['enabled'] ? 1 : 0;
            $paymentMethod->method = $formData['method'];
            $paymentMethod->description = $formData['description'];
            $paymentMethod->min_amount = (float)$formData['min_amount'];
            $paymentMethod->max_amount = (float)$formData['max_amount'];
            $paymentMethod->live_environment = $environment;
            $paymentMethod->id_shop = $shopId;

            // Handle payment fees with proper dynamic fee types
            if (isset($settings['paymentFees'])) {
                $paymentFees = $settings['paymentFees'];

                // Determine fee type based on type field from frontend
                $feeType = 0; // Default: No fee
                if ($paymentFees['enabled'] && isset($paymentFees['type'])) {
                    switch ($paymentFees['type']) {
                        case 'fixed':
                            $feeType = 1;
                            break;
                        case 'percentage':
                            $feeType = 2;
                            break;
                        case 'combined':
                            $feeType = 3;
                            break;
                        default:
                            $feeType = 0;
                    }
                }

                // Validate surcharge percentage if set
                if ($feeType === 2 || $feeType === 3) {
                    $surchargePercentage = (float)($paymentFees['percentageFee'] ?? 0);
                    if ($surchargePercentage <= -100 || $surchargePercentage >= 100) {
                        throw new MollieException($this->module->l('Surcharge percentage must be between -100% and 100%', self::FILE_NAME));
                    }
                }

                $paymentMethod->surcharge = $feeType;
                // Note: surcharge_fixed_amount_tax_incl is NOT saved to DB (not a DB field)
                $paymentMethod->surcharge_fixed_amount_tax_excl = (float)($paymentFees['fixedFeeTaxExcl'] ?? '0.00');
                $paymentMethod->surcharge_percentage = (float)($paymentFees['percentageFee'] ?? '0.00');
                $paymentMethod->surcharge_limit = (float)($paymentFees['maxFeeCap'] ?? '0.00');
                $paymentMethod->tax_rules_group_id = (int)($paymentFees['taxGroup'] ?? '0');
            }

            // Handle payment restrictions BEFORE first save (update flag on object)
            if (isset($settings['paymentRestrictions'])) {
                $restrictions = $settings['paymentRestrictions'];
                $paymentMethod->is_countries_applicable = ($restrictions['acceptFrom'] === 'selected') ? 1 : 0;
            }

            // Save payment method (this creates the record and populates the ID)
            error_log('About to save payment method with title: ' . $paymentMethod->method_name);
            error_log('About to save payment method with description: ' . $paymentMethod->description);

            if (!$paymentMethod->save()) {
                error_log('ERROR: Failed to save payment method');
                throw new MollieException($this->module->l('Failed to save payment method', self::FILE_NAME));
            }

            error_log('SUCCESS: Payment method saved with ID: ' . $paymentMethod->id);
            error_log('Saved title: ' . $paymentMethod->method_name);
            error_log('Saved description: ' . $paymentMethod->description);

            // Now handle country/customer group restrictions (requires valid ID from above save)
            if (isset($settings['paymentRestrictions'])) {
                $restrictions = $settings['paymentRestrictions'];

                // Prepare country restrictions
                $selectedCountries = [];
                $excludedCountries = [];

                if ($restrictions['acceptFrom'] === 'selected' && isset($restrictions['selectedCountries'])) {
                    $selectedCountries = $restrictions['selectedCountries'];
                }

                if (isset($restrictions['excludeCountries'])) {
                    $excludedCountries = $restrictions['excludeCountries'];
                }

                // Update country restrictions using same repository methods as old controller
                // Note: Cast to int because repository methods expect int, not string
                $this->countryRepository->updatePaymentMethodCountries((int)$paymentMethod->id, $selectedCountries);
                $this->countryRepository->updatePaymentMethodExcludedCountries((int)$paymentMethod->id, $excludedCountries);

                // Handle customer group restrictions
                if (isset($restrictions['excludeCustomerGroups'])) {
                    $this->customerRepository->updatePaymentMethodExcludedCustomerGroups((int)$paymentMethod->id, $restrictions['excludeCustomerGroups']);
                }
            }

            $result = true;

            // Save custom title translation (same logic as old PaymentMethodService)
            if (isset($settings['title']) && !empty($settings['title'])) {
                // Get all languages
                $languages = \Language::getLanguages(false, $shopId);
                foreach ($languages as $language) {
                    $this->paymentMethodLangRepository->savePaymentTitleTranslation(
                        $methodId,
                        (int)$language['id_lang'],
                        $settings['title'],
                        $shopId
                    );
                }
                error_log('Saved title translation: ' . $settings['title'] . ' for all languages');
            }

            // Save Card-specific settings (Mollie Components and One-Click Payments)
            if ($methodId === 'creditcard') {
                // Mollie Components (iframe) setting
                if (isset($settings['mollieComponents'])) {
                    $currentEnv = $environment ? 'production' : 'sandbox';
                    $configKey = \Mollie\Config\Config::MOLLIE_IFRAME[$currentEnv];
                    $this->configuration->updateValue($configKey, $settings['mollieComponents'] ? 1 : 0);
                }

                // One-Click Payments setting
                if (isset($settings['oneClickPayments'])) {
                    $currentEnv = $environment ? 'production' : 'sandbox';
                    $configKey = \Mollie\Config\Config::MOLLIE_SINGLE_CLICK_PAYMENT[$currentEnv];
                    $this->configuration->updateValue($configKey, $settings['oneClickPayments'] ? 1 : 0);
                }

                // Custom Logo setting
                if (isset($settings['useCustomLogo'])) {
                    $this->configuration->updateValue(\Mollie\Config\Config::MOLLIE_SHOW_CUSTOM_LOGO, $settings['useCustomLogo'] ? 1 : 0);
                }
                // Note: customLogoUrl is handled by the upload endpoint, not saved to config
            }

            // Save Apple Pay specific settings
            if ($methodId === 'applepay' && isset($settings['applePaySettings'])) {
                $applePaySettings = $settings['applePaySettings'];
                $this->configuration->updateValue(\Mollie\Config\Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT, $applePaySettings['directProduct'] ? 1 : 0);
                $this->configuration->updateValue(\Mollie\Config\Config::MOLLIE_APPLE_PAY_DIRECT_CART, $applePaySettings['directCart'] ? 1 : 0);
                $this->configuration->updateValue(\Mollie\Config\Config::MOLLIE_APPLE_PAY_DIRECT_STYLE, $applePaySettings['buttonStyle']);
            }

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
            // Fallback to direct database query since we don't have taxRulesGroupRepository injected
            $sql = 'SELECT id_tax_rules_group, name FROM ' . _DB_PREFIX_ . 'tax_rules_group WHERE active = 1 AND deleted = 0';
            $groups = Db::getInstance()->executeS($sql) ?: [];

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

    /**
     * Get payment fee type based on surcharge configuration
     */
    private function getPaymentFeeType($methodObj): string
    {
        $surcharge = isset($methodObj->surcharge) ? (int)$methodObj->surcharge : 0;

        switch ($surcharge) {
            case 1:
                return 'fixed';
            case 2:
                return 'percentage';
            case 3:
                return 'combined';
            default:
                return 'none';
        }
    }

    /**
     * Get custom logo URL if it exists
     */
    private function getCustomLogoUrl(): ?string
    {
        try {
            /** @var \Mollie\Provider\CreditCardLogoProvider $creditCardLogoProvider */
            $creditCardLogoProvider = $this->module->getService(\Mollie\Provider\CreditCardLogoProvider::class);

            if ($creditCardLogoProvider->logoExists()) {
                return $creditCardLogoProvider->getLogoPathUri() . '?' . time();
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to get custom logo URL', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }

        return null;
    }

    /**
     * Upload custom logo for card payment method
     */
    private function ajaxUploadCustomLogo(): void
    {
        try {
            /** @var \Mollie\Provider\CreditCardLogoProvider $creditCardLogoProvider */
            $creditCardLogoProvider = $this->module->getService(\Mollie\Provider\CreditCardLogoProvider::class);
            $targetFile = $creditCardLogoProvider->getLocalLogoPath();
            $isUploaded = 1;
            $imageFileType = pathinfo($targetFile, PATHINFO_EXTENSION);
            $returnText = '';

            // Check if file was uploaded
            if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => $this->module->l('No file uploaded or upload error', self::FILE_NAME),
                ]));

                return;
            }

            $uploadedFile = $_FILES['fileToUpload'];
            $imageFileType = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

            // Check image format
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                $returnText = $this->module->l('Upload a .jpg or .png file.', self::FILE_NAME);
                $isUploaded = 0;
            }

            // Check image dimensions (max 256x64)
            if ($isUploaded === 1) {
                $imageInfo = getimagesize($uploadedFile['tmp_name']);
                if ($imageInfo === false) {
                    $returnText = $this->module->l('Invalid image file.', self::FILE_NAME);
                    $isUploaded = 0;
                } elseif ($imageInfo[0] > 256 || $imageInfo[1] > 64) {
                    $returnText = $this->module->l('Image dimensions must be maximum 256x64 pixels.', self::FILE_NAME);
                    $isUploaded = 0;
                }
            }

            if ($isUploaded === 1) {
                // Create directory if it doesn't exist
                $targetDir = dirname($targetFile);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                // Move uploaded file
                if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
                    $returnText = basename($uploadedFile['name']);
                    $logoUrl = $creditCardLogoProvider->getLogoPathUri() . '?' . time();
                } else {
                    $isUploaded = 0;
                    $returnText = $this->module->l('Something went wrong when uploading your logo.', self::FILE_NAME);
                }
            }

            $this->ajaxRender(json_encode([
                'success' => $isUploaded === 1,
                'message' => $returnText,
                'logoUrl' => $isUploaded === 1 ? $logoUrl : null,
            ]));
        } catch (Exception $e) {
            $this->logger->error('Failed to upload custom logo', [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Failed to upload custom logo', self::FILE_NAME),
            ]));
        }
    }

    /**
     * Calculate fixed fee tax incl from tax excl + tax rules group
     * This field is not stored in DB, must be calculated on load
     */
    private function calculateFixedFeeTaxIncl($methodObj): string
    {
        if (!isset($methodObj->surcharge_fixed_amount_tax_excl) || $methodObj->surcharge_fixed_amount_tax_excl <= 0) {
            return '0.00';
        }

        $taxExcl = (float)$methodObj->surcharge_fixed_amount_tax_excl;
        $taxRulesGroupId = isset($methodObj->tax_rules_group_id) ? (int)$methodObj->tax_rules_group_id : 0;

        // If no tax rules, tax incl = tax excl
        if (!$taxRulesGroupId) {
            return number_format($taxExcl, 2, '.', '');
        }

        try {
            // Use the same TaxCalculatorProvider as the legacy system
            $taxCalculator = $this->taxCalculatorProvider->getTaxCalculator($taxRulesGroupId);
            if ($taxCalculator) {
                $taxIncl = $taxCalculator->addTaxes($taxExcl);
                return number_format($taxIncl, 2, '.', '');
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to calculate fixed fee tax incl', [
                'exception' => ExceptionUtility::getExceptions($e),
                'tax_excl' => $taxExcl,
                'tax_rules_group_id' => $taxRulesGroupId,
            ]);
        }

        // Fallback: return tax excl if calculation fails
        return number_format($taxExcl, 2, '.', '');
    }
}
