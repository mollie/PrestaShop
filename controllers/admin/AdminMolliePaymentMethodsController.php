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
use Mollie\Handler\PaymentMethod\PaymentMethodSettingsHandler;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\CustomerRepository;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;
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

    /** @var PaymentMethodSettingsHandler */
    private $paymentMethodSettingsHandler;

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
        $this->paymentMethodSettingsHandler = $this->module->getService(PaymentMethodSettingsHandler::class);
    }

    public function init(): void
    {
        parent::init();

        $version = time();

        $this->context->controller->addCSS(
            $this->module->getPathUri() . 'views/js/admin/library/dist/assets/globals.css?v=' . $version,
            'all',
            null,
            false
        );

        $jsUrl = $this->module->getPathUri() . 'views/js/admin/library/dist/assets/mollie-payment-methods.js?v=' . $version;
        $this->context->smarty->assign('molliePaymentMethodsJsUrl', $jsUrl);

        Media::addJsDef([
            'molliePaymentMethodsAjaxUrl' => $this->context->link->getAdminLink('AdminMolliePaymentMethods'),
            'mollieAjaxUrl' => $this->context->link->getAdminLink('AdminMollieAjax'),
        ]);

        Media::addJsDef([
            'molliePaymentMethodsTranslations' => [
                'paymentMethods' => $this->module->l('Payment Methods', self::FILE_NAME),
                'configurePaymentMethods' => $this->module->l('Configure Payment Methods', self::FILE_NAME),
                'enabled' => $this->module->l('Enabled', self::FILE_NAME),
                'disabled' => $this->module->l('Disabled', self::FILE_NAME),
                'enabledPaymentMethods' => $this->module->l('Enabled payment methods', self::FILE_NAME),
                'disabledPaymentMethods' => $this->module->l('Disabled payment methods', self::FILE_NAME),

                'showSettings' => $this->module->l('Show settings', self::FILE_NAME),
                'hideSettings' => $this->module->l('Hide settings', self::FILE_NAME),
                'active' => $this->module->l('Active', self::FILE_NAME),
                'inactive' => $this->module->l('Inactive', self::FILE_NAME),

                'basicSettings' => $this->module->l('Basic settings', self::FILE_NAME),
                'activateDeactivate' => $this->module->l('Activate/Deactivate', self::FILE_NAME),
                'enablePaymentMethod' => $this->module->l('Enable payment method', self::FILE_NAME),
                'useEmbeddedCreditCardForm' => $this->module->l('Use embedded credit card form in the checkout', self::FILE_NAME),
                'enableMollieComponents' => $this->module->l('Enable Mollie Components', self::FILE_NAME),
                'letCustomerSaveCreditCard' => $this->module->l('Let customer save their credit card data for future orders', self::FILE_NAME),
                'useOneClickPayments' => $this->module->l('Use one-click payments', self::FILE_NAME),
                'paymentTitle' => $this->module->l('Payment Title', self::FILE_NAME),
                'paymentTitlePlaceholder' => $this->module->l('Payment Method #1', self::FILE_NAME),

                'apiSelection' => $this->module->l('API Selection', self::FILE_NAME),
                'payments' => $this->module->l('Payments', self::FILE_NAME),
                'orders' => $this->module->l('Orders', self::FILE_NAME),
                'transactionDescription' => $this->module->l('Transaction Description', self::FILE_NAME),
                'transactionDescriptionPlaceholder' => $this->module->l('Enter transaction description', self::FILE_NAME),
                'readMore' => $this->module->l('Read more', self::FILE_NAME),
                'aboutDifferences' => $this->module->l('about the differences between Payments and Orders API', self::FILE_NAME),

                'useCustomLogo' => $this->module->l('Use custom logo', self::FILE_NAME),
                'uploadLogo' => $this->module->l('Upload Logo', self::FILE_NAME),
                'replaceLogo' => $this->module->l('Replace Logo', self::FILE_NAME),
                'logoUploadHelp' => $this->module->l('Upload a JPG or PNG image. Maximum dimensions: 256x64 pixels. Maximum file size: 2MB.', self::FILE_NAME),

                'applePayDirectSettings' => $this->module->l('Apple Pay Direct settings', self::FILE_NAME),
                'applePayDirectProductPage' => $this->module->l('Apple Pay Direct product page', self::FILE_NAME),
                'enableApplePayProductPages' => $this->module->l('Enable Apple Pay on product pages', self::FILE_NAME),
                'applePayDirectCartPage' => $this->module->l('Apple Pay Direct cart page', self::FILE_NAME),
                'enableApplePayCartPages' => $this->module->l('Enable Apple Pay on cart pages', self::FILE_NAME),
                'applePayDirectButtonStyle' => $this->module->l('Apple Pay Direct button style', self::FILE_NAME),
                'applePayButtonBlack' => $this->module->l('Black', self::FILE_NAME),
                'applePayButtonOutline' => $this->module->l('Outline', self::FILE_NAME),
                'applePayButtonWhite' => $this->module->l('White', self::FILE_NAME),

                'paymentRestrictions' => $this->module->l('Payment restrictions', self::FILE_NAME),
                'acceptPaymentsFrom' => $this->module->l('Accept payments from', self::FILE_NAME),
                'allCountries' => $this->module->l('All countries', self::FILE_NAME),
                'selectedCountries' => $this->module->l('Selected countries', self::FILE_NAME),
                'acceptPaymentsFromSpecificCountries' => $this->module->l('Accept payments from specific countries', self::FILE_NAME),
                'selectCountriesAccept' => $this->module->l('Select countries to accept payments from', self::FILE_NAME),
                'excludePaymentsFromCountries' => $this->module->l('Exclude payments from specific countries', self::FILE_NAME),
                'selectCountriesToExclude' => $this->module->l('Select countries to exclude', self::FILE_NAME),
                'excludeCustomerGroups' => $this->module->l('Restrict to customer groups', self::FILE_NAME),
                'selectCustomerGroups' => $this->module->l('Select customer groups that will not see this payment method', self::FILE_NAME),
                'customerGroupsHelp' => $this->module->l('If no groups are selected, the payment method will be available to all customers.', self::FILE_NAME),
                'guest' => $this->module->l('Guest', self::FILE_NAME),
                'customerGroup' => $this->module->l('Customer Group', self::FILE_NAME),

                'paymentFees' => $this->module->l('Payment fees', self::FILE_NAME),
                'enablePaymentFee' => $this->module->l('Enable payment fee', self::FILE_NAME),
                'paymentFeeType' => $this->module->l('Payment fee type', self::FILE_NAME),
                'fixedFee' => $this->module->l('Fixed fee', self::FILE_NAME),
                'fixedFeeTaxIncl' => $this->module->l('Fixed fee (tax incl)', self::FILE_NAME),
                'fixedFeeTaxExcl' => $this->module->l('Fixed fee (tax excl)', self::FILE_NAME),
                'percentageFee' => $this->module->l('Percentage', self::FILE_NAME),
                'percentageFeeLabel' => $this->module->l('Percentage fee', self::FILE_NAME),
                'combinedFee' => $this->module->l('Combined payment surcharge limit', self::FILE_NAME),
                'noFee' => $this->module->l('No fee', self::FILE_NAME),
                'paymentFeeTaxGroup' => $this->module->l('Payment fee tax group', self::FILE_NAME),
                'taxRulesGroupForFixedFee' => $this->module->l('Tax rules group for fixed fee', self::FILE_NAME),
                'maximumFee' => $this->module->l('Maximum fee', self::FILE_NAME),
                'minimumAmount' => $this->module->l('Minimum amount', self::FILE_NAME),
                'maximumAmount' => $this->module->l('Maximum amount', self::FILE_NAME),
                'minOrderAmount' => $this->module->l('Min order amount', self::FILE_NAME),
                'maxOrderAmount' => $this->module->l('Max order amount', self::FILE_NAME),
                'paymentFeeEmailHelp' => $this->module->l('Add "(payment_fee)" in email translations to display it in your email template.', self::FILE_NAME),

                'orderRestrictions' => $this->module->l('Order restrictions', self::FILE_NAME),


                'save' => $this->module->l('Save', self::FILE_NAME),
                'saving' => $this->module->l('Saving...', self::FILE_NAME),
                'loadingMethods' => $this->module->l('Loading payment methods...', self::FILE_NAME),
                'loadingError' => $this->module->l('Failed to load payment methods', self::FILE_NAME),
                'saveSettings' => $this->module->l('Save Settings', self::FILE_NAME),

                'transactionDescriptionHelp' => $this->module->l('Use any of the following variables to create a transaction description for payments that use this method:', self::FILE_NAME),
                'transactionDescriptionVariables' => $this->module->l('{orderNumber}, {storeName}, {countryCode}, {cart.id}, {order.reference}, {customer.firstname}, {customer.lastname}, {customer.company}', self::FILE_NAME),


                'paymentMethodNotFound' => $this->module->l('Payment method not found', self::FILE_NAME),
                'settingsSavedSuccessfully' => $this->module->l('Settings saved successfully!', self::FILE_NAME),
                'failedToSaveSettings' => $this->module->l('Failed to save settings', self::FILE_NAME),
                'paymentMethodsOrderUpdated' => $this->module->l('Payment methods order updated successfully!', self::FILE_NAME),
                'failedToUpdateOrder' => $this->module->l('Failed to update payment methods order', self::FILE_NAME),
                'savingNewOrder' => $this->module->l('Saving new order...', self::FILE_NAME),
                'noPaymentMethods' => $this->module->l('No payment methods', self::FILE_NAME),
                'paymentMethodsWillAppear' => $this->module->l('Payment methods will appear here once configured', self::FILE_NAME),

                'pleaseUploadJpgOrPng' => $this->module->l('Please upload a JPG or PNG file', self::FILE_NAME),
                'fileSizeTooLarge' => $this->module->l('File size must be less than 2MB', self::FILE_NAME),
                'imageDimensionsTooLarge' => $this->module->l('Image dimensions must be maximum 256x64 pixels', self::FILE_NAME),
                'failedToUploadLogo' => $this->module->l('Failed to upload logo. Please try again.', self::FILE_NAME),
                'invalidImageFile' => $this->module->l('Invalid image file', self::FILE_NAME),
                'uploading' => $this->module->l('Uploading...', self::FILE_NAME),
                'customLogoPreview' => $this->module->l('Custom logo preview', self::FILE_NAME),
                'logoUploadedSuccessfully' => $this->module->l('Logo uploaded successfully!', self::FILE_NAME),
                'customLogo' => $this->module->l('Custom Logo', self::FILE_NAME),
                'remove' => $this->module->l('Remove', self::FILE_NAME),

                'applePayButtonBlackDesc' => $this->module->l('Black Apple Pay button', self::FILE_NAME),
                'applePayButtonOutlineDesc' => $this->module->l('White with outline', self::FILE_NAME),
                'applePayButtonWhiteDesc' => $this->module->l('White Apple Pay button', self::FILE_NAME),


                'selectOption' => $this->module->l('Select option', self::FILE_NAME),
                'selectOptions' => $this->module->l('Select options', self::FILE_NAME),
                'itemsSelected' => $this->module->l('%s selected', self::FILE_NAME),

                'dragPaymentOptionsToReorder' => $this->module->l('Drag payment options to reorder', self::FILE_NAME),
            ],
        ]);

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

    private function ajaxGetPaymentMethods(): void
    {
        try {
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

            $formattedMethods = [];
            foreach ($apiMethods as $method) {
                try {
                    $methodId = $method['id'];
                    $methodObj = $method['obj'];

                    if (!$methodObj) {
                        $this->logger->warning('Method object is null for method: ' . $methodId);
                        continue;
                    }

                    $formattedMethods[] = [
                        'id' => $methodId,
                        'name' => $method['name'],
                        'type' => $methodId === 'creditcard' ? 'card' : 'other',
                        'status' => (isset($methodObj->enabled) && $methodObj->enabled) ? 'active' : 'inactive',
                        'isExpanded' => false,
                        'position' => (int) (isset($methodObj->position) ? $methodObj->position : 0),
                        'image' => $method['image'] ?? null,
                        'settings' => [
                            'enabled' => (bool) (isset($methodObj->enabled) ? $methodObj->enabled : false),
                            'title' => $this->getPaymentMethodTitle($methodId, $method['name'] ?? ''),
                            'mollieComponents' => $methodId === 'creditcard' ? $this->getCreditCardMollieComponentsSetting($methodObj) : true,
                            'oneClickPayments' => $methodId === 'creditcard' ? $this->getCreditCardOneClickSetting($methodObj) : false,
                            'transactionDescription' => (isset($methodObj->description) && $methodObj->description) ? $methodObj->description : '{orderNumber}',
                            'apiSelection' => (isset($methodObj->method) && $methodObj->method === 'orders') ? 'orders' : 'payments',
                            'useCustomLogo' => $methodId === 'creditcard' ? (bool) ($this->configuration->get(\Mollie\Config\Config::MOLLIE_SHOW_CUSTOM_LOGO) ?: 0) : false,
                            'customLogoUrl' => $methodId === 'creditcard' ? $this->getCustomLogoUrl() : null,
                            'paymentRestrictions' => [
                                'acceptFrom' => (isset($methodObj->is_countries_applicable) && $methodObj->is_countries_applicable) ? 'selected' : 'all',
                                'selectedCountries' => $method['countries'] ?? [],
                                'excludeCountries' => $method['excludedCountries'] ?? [],
                                'excludeCustomerGroups' => $method['excludedCustomerGroups'] ?? [],
                            ],
                            'paymentFees' => [
                                'enabled' => (int) (isset($methodObj->surcharge) ? $methodObj->surcharge : 0) > 0,
                                'type' => $this->getPaymentFeeType($methodObj),
                                'taxGroup' => isset($methodObj->tax_rules_group_id) ? (string) $methodObj->tax_rules_group_id : '0',
                                'fixedFeeTaxIncl' => $this->calculateFixedFeeTaxIncl($methodObj),
                                'fixedFeeTaxExcl' => isset($methodObj->surcharge_fixed_amount_tax_excl) ? $methodObj->surcharge_fixed_amount_tax_excl : '0.00',
                                'percentageFee' => isset($methodObj->surcharge_percentage) ? $methodObj->surcharge_percentage : '0.00',
                                'maxFeeCap' => isset($methodObj->surcharge_limit) ? $methodObj->surcharge_limit : '0.00',
                            ],
                            'orderRestrictions' => [
                                'minAmount' => (isset($methodObj->min_amount) && $methodObj->min_amount > 0)
                                    ? $methodObj->min_amount
                                    : '',
                                'maxAmount' => (isset($methodObj->max_amount) && $methodObj->max_amount > 0)
                                    ? $methodObj->max_amount
                                    : '',
                                'apiMinAmount' => $method['minimumAmount'] ? $method['minimumAmount']['value'] : null,
                                'apiMaxAmount' => $method['maximumAmount'] ? $method['maximumAmount']['value'] : null,
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
                    continue;
                }
            }

            usort($formattedMethods, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            $responseData = [
                'success' => true,
                'data' => [
                    'methods' => $formattedMethods,
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

            $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

            if (!$paymentMethodId) {
                throw new MollieException($this->module->l('Payment method not found', self::FILE_NAME));
            }

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

    private function ajaxUpdateMethodsOrder(): void
    {
        try {
            $methodIds = json_decode($this->tools->getValue('method_ids'), true);

            if (!is_array($methodIds)) {
                throw new MollieException($this->module->l('Invalid method IDs provided', self::FILE_NAME));
            }

            $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
            $shopId = $this->context->shop->id;

            $updatedCount = 0;
            foreach ($methodIds as $position => $methodId) {
                $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($methodId, $environment, $shopId);

                if ($paymentMethodId) {
                    $paymentMethod = new MolPaymentMethod((int) $paymentMethodId);
                    $paymentMethod->position = $position + 1;
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

    private function ajaxRefreshMethods(): void
    {
        try {
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

            $currentMethods = $this->paymentMethodRepository->getMethodsForCheckout($environment, $shopId) ?: [];
            $currentMethodIds = array_column($currentMethods, 'id_method');

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

    private function getPaymentMethodTitle(string $methodId, string $defaultName): string
    {
        try {
            $langId = (int) $this->context->language->id;
            $shopId = $this->context->shop->id;

            $translation = $this->paymentMethodLangRepository->findOneBy([
                'id_method' => $methodId,
                'id_lang' => $langId,
                'id_shop' => $shopId,
            ]);

            if ($translation && isset($translation->text) && !empty($translation->text)) {
                return $translation->text;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting payment method title', [
                'method_id' => $methodId,
                'exception' => $e->getMessage(),
            ]);
        }

        return $defaultName;
    }

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
            $shopId = $this->context->shop->id;

            $this->paymentMethodSettingsHandler->handlePaymentMethodSave($methodId, $settings, $environment, $shopId);

            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('Payment method settings saved successfully', self::FILE_NAME),
            ]));
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

    private function getTaxRulesGroups(): array
    {
        $taxRulesGroups = [];

        try {
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

    private function getPaymentFeeType($methodObj): string
    {
        $surcharge = isset($methodObj->surcharge) ? (int) $methodObj->surcharge : 0;

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

    private function getCustomLogoUrl(): ?string
    {
        try {
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

    private function ajaxUploadCustomLogo(): void
    {
        try {
            $creditCardLogoProvider = $this->module->getService(\Mollie\Provider\CreditCardLogoProvider::class);
            $targetFile = $creditCardLogoProvider->getLocalLogoPath();
            $isUploaded = 1;
            $imageFileType = pathinfo($targetFile, PATHINFO_EXTENSION);
            $returnText = '';

            if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'message' => $this->module->l('No file uploaded or upload error', self::FILE_NAME),
                ]));

                return;
            }

            $uploadedFile = $_FILES['fileToUpload'];
            $imageFileType = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                $returnText = $this->module->l('Upload a .jpg or .png file.', self::FILE_NAME);
                $isUploaded = 0;
            }

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

            $logoUrl = null;
            if ($isUploaded === 1) {
                $targetDir = dirname($targetFile);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

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
                'logoUrl' => $logoUrl,
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

    private function calculateFixedFeeTaxIncl($methodObj): string
    {
        if (!isset($methodObj->surcharge_fixed_amount_tax_excl) || $methodObj->surcharge_fixed_amount_tax_excl <= 0) {
            return '0.00';
        }

        $taxExcl = (float) $methodObj->surcharge_fixed_amount_tax_excl;
        $taxRulesGroupId = isset($methodObj->tax_rules_group_id) ? (int) $methodObj->tax_rules_group_id : 0;

        if (!$taxRulesGroupId) {
            return number_format($taxExcl, 2, '.', '');
        }

        try {
            $address = new Address();
            if (isset($this->context->cart->id_address_delivery)) {
                $address = new Address((int) $this->context->cart->id_address_delivery);
            }

            $taxManager = TaxManagerFactory::getManager($address, $taxRulesGroupId);
            $taxCalculator = $taxManager->getTaxCalculator();

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

        return number_format($taxExcl, 2, '.', '');
    }

    private function getCreditCardMollieComponentsSetting($methodObj): bool
    {
        $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
        $currentEnv = $environment ? 'production' : 'sandbox';
        $configKey = Config::MOLLIE_IFRAME[$currentEnv];
        $value = (bool) ($this->configuration->get($configKey) ?: 0);

        return $value;
    }

    private function getCreditCardOneClickSetting($methodObj): bool
    {
        $environment = (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
        $currentEnv = $environment ? 'production' : 'sandbox';
        $configKey = Config::MOLLIE_SINGLE_CLICK_PAYMENT[$currentEnv];
        $value = (bool) ($this->configuration->get($configKey) ?: 0);

        return $value;
    }
}
