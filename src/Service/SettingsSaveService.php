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

namespace Mollie\Service;

use Carrier;
use Exception;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Handler\Certificate\CertificateHandlerInterface;
use Mollie\Handler\Certificate\Exception\ApplePayDirectCertificateCreation;
use Mollie\Handler\Settings\PaymentMethodPositionHandlerInterface;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Utility\TagsUtility;
use MolPaymentMethodIssuer;
use OrderState;
use PrestaShopDatabaseException;
use PrestaShopException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SettingsSaveService
{
    const FILE_NAME = 'SettingsSaveService';

    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var PaymentMethodService
     */
    private $paymentMethodService;

    /**
     * @var ApiKeyService
     */
    private $apiKeyService;

    /**
     * @var MolCarrierInformationService
     */
    private $carrierInformationService;

    /**
     * @var PaymentMethodPositionHandlerInterface
     */
    private $paymentMethodPositionHandler;

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * @var CertificateHandlerInterface
     */
    private $applePayDirectCertificateHandler;

    private $configurationAdapter;
    /** @var Context */
    private $context;
    /** @var ToolsAdapter */
    private $tools;

    public function __construct(
        Mollie $module,
        CountryRepository $countryRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodService $paymentMethodService,
        ApiService $apiService,
        MolCarrierInformationService $carrierInformationService,
        PaymentMethodPositionHandlerInterface $paymentMethodPositionHandler,
        ApiKeyService $apiKeyService,
        CertificateHandlerInterface $applePayDirectCertificateHandler,
        ConfigurationAdapter $configurationAdapter,
        Context $context,
        ToolsAdapter $tools
    ) {
        $this->module = $module;
        $this->countryRepository = $countryRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->apiKeyService = $apiKeyService;
        $this->carrierInformationService = $carrierInformationService;
        $this->paymentMethodPositionHandler = $paymentMethodPositionHandler;
        $this->apiService = $apiService;
        $this->applePayDirectCertificateHandler = $applePayDirectCertificateHandler;
        $this->configurationAdapter = $configurationAdapter;
        $this->context = $context;
        $this->tools = $tools;
    }

    /**
     * @param array $errors
     *
     * @return array
     *
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function saveSettings(&$errors = [])
    {
        $oldEnvironment = (int) $this->configurationAdapter->get(Config::MOLLIE_ENVIRONMENT);
        $environment = (int) $this->tools->getValue(Config::MOLLIE_ENVIRONMENT);
        $mollieApiKey = $this->tools->getValue(Config::MOLLIE_API_KEY);
        $mollieApiKeyTest = $this->tools->getValue(Config::MOLLIE_API_KEY_TEST);
        $paymentOptionPositions = $this->tools->getValue(Config::MOLLIE_FORM_PAYMENT_OPTION_POSITION);

        $apiKey = Config::ENVIRONMENT_LIVE === (int) $environment ? $mollieApiKey : $mollieApiKeyTest;
        $isApiKeyIncorrect = 0 !== strpos($apiKey, 'live') && 0 !== strpos($apiKey, 'test');

        if ($isApiKeyIncorrect) {
            $errors[] = $this->module->l('The API key needs to start with test or live.', self::FILE_NAME);

            return $errors;
        }

        if ($this->tools->getValue(Config::METHODS_CONFIG) && json_decode($this->tools->getValue(Config::METHODS_CONFIG))) {
            $this->configurationAdapter->updateValue(
                Config::METHODS_CONFIG,
                json_encode(@json_decode($this->tools->getValue(Config::METHODS_CONFIG)))
            );
        }

        if ((int) $this->tools->getValue(Config::MOLLIE_ENV_CHANGED) === 1) {
            $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY, $mollieApiKey);
            $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY_TEST, $mollieApiKeyTest);
            $this->configurationAdapter->updateValue(Config::MOLLIE_ENVIRONMENT, $environment);

            try {
                $api = $this->apiKeyService->setApiKey($apiKey, $this->module->version);
                if (null === $api) {
                    throw new MollieException('Failed to connect to mollie API', MollieException::API_CONNECTION_EXCEPTION);
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY, null);

                return [$this->module->l('Wrong API Key!', self::FILE_NAME)];
            }

            return [];
        }

        if ($oldEnvironment === $environment && $apiKey && $this->module->getApiClient() !== null) {
            $savedPaymentMethods = [];
            foreach ($this->apiService->getMethodsForConfig($this->module->getApiClient()) as $method) {
                $paymentMethodId = $method['obj']->id;
                try {
                    $paymentMethod = $this->paymentMethodService->savePaymentMethod($method);
                    $savedPaymentMethods[] = $paymentMethod->id_method;
                } catch (Exception $e) {
                    $errors[] = $this->module->l('Something went wrong. Couldn\'t save your payment methods', self::FILE_NAME) . ":{$method['id']}";
                    continue;
                }

                if (!$this->paymentMethodRepository->deletePaymentMethodIssuersByPaymentMethodId($paymentMethod->id)) {
                    $errors[] = $this->module->l('Something went wrong. Couldn\'t delete old payment methods issuers', self::FILE_NAME) . ":{$method['id']}";
                    continue;
                }

                if ($method['issuers']) {
                    $paymentMethodIssuer = new MolPaymentMethodIssuer();
                    $paymentMethodIssuer->issuers_json = json_encode($method['issuers']);
                    $paymentMethodIssuer->id_payment_method = $paymentMethod->id;
                    try {
                        $paymentMethodIssuer->add();
                    } catch (Exception $e) {
                        $errors[] = $this->module->l('Something went wrong. Couldn\'t save your payment methods issuer', self::FILE_NAME);
                    }
                }

                $countries = $this->tools->getValue(Config::MOLLIE_METHOD_CERTAIN_COUNTRIES . $method['id']);
                $excludedCountries = $this->tools->getValue(
                    Config::MOLLIE_METHOD_EXCLUDE_CERTAIN_COUNTRIES . $method['id']
                );
                $this->countryRepository->updatePaymentMethodCountries($paymentMethodId, $countries);
                $this->countryRepository->updatePaymentMethodExcludedCountries($paymentMethodId, $excludedCountries);
            }
            $this->paymentMethodRepository->deleteOldPaymentMethods($savedPaymentMethods, $environment, $this->context->getShopId());
        }

        if ($paymentOptionPositions) {
            $this->paymentMethodPositionHandler->savePositions($paymentOptionPositions);
        }

        $useCustomLogo = $this->tools->getValue(Config::MOLLIE_SHOW_CUSTOM_LOGO);
        $this->configurationAdapter->updateValue(
            Config::MOLLIE_SHOW_CUSTOM_LOGO,
            $useCustomLogo
        );

        $isApplePayDirectProductEnabled = (int) $this->tools->getValue('MOLLIE_APPLE_PAY_DIRECT_PRODUCT_ENABLED');
        $isApplePayDirectCartEnabled = (int) $this->tools->getValue('MOLLIE_APPLE_PAY_DIRECT_CART_ENABLED');

        if ($isApplePayDirectProductEnabled || $isApplePayDirectCartEnabled) {
            try {
                $this->applePayDirectCertificateHandler->handle();
            } catch (ApplePayDirectCertificateCreation $e) {
                $isApplePayDirectProductEnabled = false;
                $isApplePayDirectCartEnabled = false;

                $errors[] = $e->getMessage();
                $errors[] = TagsUtility::ppTags(
                    $this->module->l('Grant permissions for the folder or visit [1]ApplePay[/1] to see how it can be added manually', self::FILE_NAME),
                    [$this->module->display($this->module->getPathUri(), 'views/templates/admin/applePayDirectDocumentation.tpl')]
                );
            }
        }

        $molliePaymentscreenLocale = $this->tools->getValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE);
        $mollieOrderConfirmationSand = $this->tools->getValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION);
        $mollieIFrameEnabled = $this->tools->getValue(Config::MOLLIE_IFRAME[$environment ? 'production' : 'sandbox']);
        $mollieSingleClickPaymentEnabled = $this->tools->getValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT[$environment ? 'production' : 'sandbox']);
        $mollieImages = $this->tools->getValue(Config::MOLLIE_IMAGES);
        $showResentPayment = $this->tools->getValue(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK);
        $mollieIssuers = $this->tools->getValue(Config::MOLLIE_ISSUERS[$environment ? 'production' : 'sandbox']);
        $mollieCss = $this->tools->getValue(Config::MOLLIE_CSS);

        if (!isset($mollieCss)) {
            $mollieCss = '';
        }

        $mollieLogger = $this->tools->getValue(Config::MOLLIE_DEBUG_LOG);
        $mollieApi = $this->tools->getValue(Config::MOLLIE_API);
        $mollieMethodCountriesEnabled = (int) $this->tools->getValue(Config::MOLLIE_METHOD_COUNTRIES);
        $mollieMethodCountriesDisplayEnabled = (int) $this->tools->getValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY);
        $mollieErrors = $this->tools->getValue(Config::MOLLIE_DISPLAY_ERRORS);
        $voucherCategory = $this->tools->getValue(Config::MOLLIE_VOUCHER_CATEGORY);
        $applePayDirectStyle = $this->tools->getValue(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE);
        $isBancontactQrCodeEnabled = $this->tools->getValue(Config::MOLLIE_BANCONTACT_QR_CODE_ENABLED);

        $mollieShipMain = $this->tools->getValue(Config::MOLLIE_AUTO_SHIP_MAIN);
        if (!isset($mollieErrors)) {
            $mollieErrors = false;
        } else {
            $mollieErrors = (1 == $mollieErrors);
        }

        $apiKey = Config::ENVIRONMENT_LIVE === (int) $environment ?
            $mollieApiKey : $mollieApiKeyTest;

        if ($apiKey) {
            try {
                $api = $this->apiKeyService->setApiKey($apiKey, $this->module->version);
                if (null === $api) {
                    throw new MollieException('Failed to connect to mollie API', MollieException::API_CONNECTION_EXCEPTION);
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY, null);

                return [$this->module->l('Wrong API Key!', self::FILE_NAME)];
            }
        }
        try {
            $this->handleAuthorizablePaymentInvoiceStatus();
        } catch (Exception $e) {
            $errors[] = $this->module->l('There are issues with your authorizable payment statuses, please try resetting Mollie module.', self::FILE_NAME);
        }

        if (empty($errors)) {
            if ($isBancontactQrCodeEnabled !== false) {
                $this->configurationAdapter->updateValue(Config::MOLLIE_BANCONTACT_QR_CODE_ENABLED, $isBancontactQrCodeEnabled);
            }

            $this->configurationAdapter->updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT, $isApplePayDirectProductEnabled);
            $this->configurationAdapter->updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_CART, $isApplePayDirectCartEnabled);
            $this->configurationAdapter->updateValue(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE, $applePayDirectStyle);
            $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY, $mollieApiKey);
            $this->configurationAdapter->updateValue(Config::MOLLIE_API_KEY_TEST, $mollieApiKeyTest);
            $this->configurationAdapter->updateValue(Config::MOLLIE_ENVIRONMENT, $environment);
            $this->configurationAdapter->updateValue(Config::MOLLIE_PAYMENTSCREEN_LOCALE, $molliePaymentscreenLocale);
            $this->configurationAdapter->updateValue(Config::MOLLIE_SEND_ORDER_CONFIRMATION, $mollieOrderConfirmationSand);
            $this->configurationAdapter->updateValue(Config::MOLLIE_IFRAME, $mollieIFrameEnabled);
            $this->configurationAdapter->updateValue(Config::MOLLIE_SINGLE_CLICK_PAYMENT, $mollieSingleClickPaymentEnabled);
            $this->configurationAdapter->updateValue(Config::MOLLIE_IMAGES, $mollieImages);
            $this->configurationAdapter->updateValue(Config::MOLLIE_SHOW_RESEND_PAYMENT_LINK, $showResentPayment);
            $this->configurationAdapter->updateValue(Config::MOLLIE_ISSUERS, $mollieIssuers);
            $this->configurationAdapter->updateValue(Config::MOLLIE_METHOD_COUNTRIES, (int) $mollieMethodCountriesEnabled);
            $this->configurationAdapter->updateValue(Config::MOLLIE_METHOD_COUNTRIES_DISPLAY, (int) $mollieMethodCountriesDisplayEnabled);
            $this->configurationAdapter->updateValue(Config::MOLLIE_CSS, $mollieCss);
            $this->configurationAdapter->updateValue(Config::MOLLIE_DISPLAY_ERRORS, (int) $mollieErrors);
            $this->configurationAdapter->updateValue(Config::MOLLIE_DEBUG_LOG, (int) $mollieLogger);
            $this->configurationAdapter->updateValue(Config::MOLLIE_API, $mollieApi);
            $this->configurationAdapter->updateValue(Config::MOLLIE_VOUCHER_CATEGORY, $voucherCategory);
            $this->configurationAdapter->updateValue(
                Config::MOLLIE_AUTO_SHIP_STATUSES,
                json_encode($this->getStatusesValue(Config::MOLLIE_AUTO_SHIP_STATUSES))
            );
            $this->configurationAdapter->updateValue(Config::MOLLIE_AUTO_SHIP_MAIN, (int) $mollieShipMain);
            $this->configurationAdapter->updateValue(
                Config::MOLLIE_TRACKING_URLS,
                json_encode(@json_decode($this->tools->getValue(Config::MOLLIE_TRACKING_URLS)))
            );
            $carriers = Carrier::getCarriers(
                $this->context->getLanguageId(),
                false,
                false,
                false,
                null,
                Carrier::ALL_CARRIERS
            );
            foreach ($carriers as $carrier) {
                $urlSource = $this->tools->getValue(Config::MOLLIE_CARRIER_URL_SOURCE . $carrier['id_carrier']);
                $customUrl = $this->tools->getValue(Config::MOLLIE_CARRIER_CUSTOM_URL . $carrier['id_carrier']);
                $this->carrierInformationService->saveMolCarrierInfo($carrier['id_carrier'], $urlSource, $customUrl);
            }

            foreach (array_keys(Config::getStatuses()) as $name) {
                $name = strtoupper(strtolower($name));
                if (!$this->tools->getValue("MOLLIE_STATUS_{$name}")) {
                    continue;
                }
                $new = (int) $this->tools->getValue("MOLLIE_STATUS_{$name}");
                $this->configurationAdapter->updateValue("MOLLIE_STATUS_{$name}", $new);
                Config::getStatuses()[strtolower(strtoupper($name))] = $new;

                if (PaymentStatus::STATUS_OPEN != $name) {
                    $this->configurationAdapter->updateValue(
                        "MOLLIE_MAIL_WHEN_{$name}",
                        (bool) $this->tools->getValue("MOLLIE_MAIL_WHEN_{$name}")
                    );
                }
            }

            $resultMessage[] = $this->module->l('The configuration has been saved!', self::FILE_NAME);
        } else {
            $resultMessage = [];
            foreach ($errors as $error) {
                $resultMessage[] = $error;
            }
        }

        return $resultMessage;
    }

    /**
     * Get all status values from the form.
     *
     * @param string $key The key that is used in the HelperForm
     *
     * @return array Array with statuses
     *
     * @since 3.3.0
     */
    private function getStatusesValue($key)
    {
        $statesEnabled = [];

        foreach (OrderState::getOrderStates($this->context->getLanguageId()) as $state) {
            if ($this->tools->isSubmit($key . '_' . $state['id_order_state'])) {
                $statesEnabled[] = $state['id_order_state'];
            }
        }

        return $statesEnabled;
    }

    private function handleAuthorizablePaymentInvoiceStatus(): void
    {
        $authorizablePaymentInvoiceOnStatus = $this->tools->getValue(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS);

        $this->configurationAdapter->updateValue(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS, $authorizablePaymentInvoiceOnStatus);

        if (Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED === $authorizablePaymentInvoiceOnStatus) {
            $this->updateAuthorizablePaymentOrderStatus(true);

            return;
        }

        $this->updateAuthorizablePaymentOrderStatus(false);
    }

    private function updateAuthorizablePaymentOrderStatus(bool $isShipped): void
    {
        $authorizablePaymentStatusShippedId = $this->configurationAdapter->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED);
        $authorizablePaymentStatusShipped = new OrderState((int) $authorizablePaymentStatusShippedId);

        $authorizablePaymentStatusShipped->invoice = $isShipped;
        $authorizablePaymentStatusShipped->update();

        $authorizablePaymentStatusAuthorizedId = $this->configurationAdapter->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_AUTHORIZED);
        $authorizablePaymentStatusAuthorized = new OrderState((int) $authorizablePaymentStatusAuthorizedId);

        $authorizablePaymentStatusAuthorized->invoice = !$isShipped;
        $authorizablePaymentStatusAuthorized->update();
    }
}
