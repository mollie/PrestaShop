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

namespace Mollie\Service\PaymentMethod;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Logger\LoggerInterface;
use Mollie\Provider\PaymentMethod\PaymentMethodConfigProvider;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;
use Mollie\Service\CountryService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodFormatterService
{
    /** @var PaymentMethodLangRepositoryInterface */
    private $paymentMethodLangRepository;

    /** @var CountryService */
    private $countryService;

    /** @var PaymentMethodConfigProvider */
    private $configProvider;

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var LoggerInterface */
    private $logger;

    /** @var \Context */
    private $context;

    public function __construct(
        PaymentMethodLangRepositoryInterface $paymentMethodLangRepository,
        CountryService $countryService,
        PaymentMethodConfigProvider $configProvider,
        ConfigurationAdapter $configuration,
        LoggerInterface $logger,
        \Context $context
    ) {
        $this->paymentMethodLangRepository = $paymentMethodLangRepository;
        $this->countryService = $countryService;
        $this->configProvider = $configProvider;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->context = $context;
    }

    /**
     * Format payment methods from API for frontend consumption
     *
     * @param array $apiMethods Methods from Mollie API
     *
     * @return array Formatted methods
     */
    public function formatPaymentMethodsForFrontend(array $apiMethods): array
    {
        $formattedMethods = [];

        foreach ($apiMethods as $method) {
            try {
                $methodId = $method['id'];
                $methodObj = $method['obj'] ?? null;

                if (!$methodObj) {
                    $this->logger->warning('Method object is null for method: ' . $methodId);
                    continue;
                }

                $formattedMethods[] = $this->formatSinglePaymentMethod($method, $methodObj);
            } catch (\Exception $e) {
                $this->logger->error('Error formatting payment method: ' . ($method['id'] ?? 'unknown'), [
                    'exception' => ExceptionUtility::getExceptions($e),
                    'method_data' => $method,
                ]);
                continue;
            }
        }

        // Sort by position
        usort($formattedMethods, function ($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        return $formattedMethods;
    }

    /**
     * Format a single payment method
     *
     * @param array $method API method data
     * @param \MolPaymentMethod $methodObj Payment method object
     *
     * @return array Formatted method
     */
    private function formatSinglePaymentMethod(array $method, $methodObj): array
    {
        $methodId = $method['id'];

        return [
            'id' => $methodId,
            'name' => $method['name'] ?? '',
            'type' => $methodId === 'creditcard' ? 'card' : 'other',
            'status' => (isset($methodObj->enabled) && $methodObj->enabled) ? 'active' : 'inactive',
            'isExpanded' => false,
            'position' => (int) (isset($methodObj->position) ? $methodObj->position : 0),
            'image' => $method['image'] ?? null,
            'settings' => [
                'enabled' => (bool) (isset($methodObj->enabled) ? $methodObj->enabled : false),
                'title' => $this->getPaymentMethodTitle($methodId, $method['name'] ?? ''),
                'mollieComponents' => $methodId === 'creditcard'
                    ? $this->getCreditCardMollieComponentsSetting($methodObj)
                    : true,
                'oneClickPayments' => $methodId === 'creditcard'
                    ? $this->getCreditCardOneClickSetting($methodObj)
                    : false,
                'transactionDescription' => (isset($methodObj->description) && $methodObj->description)
                    ? $methodObj->description
                    : '{orderNumber}',
                'apiSelection' => (isset($methodObj->method) && $methodObj->method === 'orders')
                    ? 'orders'
                    : 'payments',
                'useCustomLogo' => $methodId === 'creditcard'
                    ? (bool) ($this->configuration->get(Config::MOLLIE_SHOW_CUSTOM_LOGO) ?: 0)
                    : false,
                'customLogoUrl' => $methodId === 'creditcard'
                    ? $this->configProvider->getCustomLogoUrl()
                    : null,
                'paymentRestrictions' => $this->formatPaymentRestrictions($methodObj, $method),
                'paymentFees' => $this->formatPaymentFees($methodObj),
                'orderRestrictions' => $this->formatOrderRestrictions($methodObj, $method),
                'applePaySettings' => $methodId === 'applepay'
                    ? $this->formatApplePaySettings()
                    : null,
            ],
        ];
    }

    /**
     * Format payment restrictions
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     * @param array $method API method data
     *
     * @return array Formatted restrictions
     */
    private function formatPaymentRestrictions($methodObj, array $method): array
    {
        return [
            'acceptFrom' => (isset($methodObj->is_countries_applicable) && $methodObj->is_countries_applicable)
                ? 'selected'
                : 'all',
            'selectedCountries' => $method['countries'] ?? [],
            'excludeCountries' => $method['excludedCountries'] ?? [],
            'excludeCustomerGroups' => $method['excludedCustomerGroups'] ?? [],
        ];
    }

    /**
     * Format payment fees
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     *
     * @return array Formatted fees
     */
    private function formatPaymentFees($methodObj): array
    {
        return [
            'enabled' => (int) (isset($methodObj->surcharge) ? $methodObj->surcharge : 0) > 0,
            'type' => $this->configProvider->getPaymentFeeType($methodObj),
            'taxGroup' => isset($methodObj->tax_rules_group_id)
                ? (string) $methodObj->tax_rules_group_id
                : '0',
            'fixedFeeTaxIncl' => $this->configProvider->calculateFixedFeeTaxIncl($methodObj),
            'fixedFeeTaxExcl' => isset($methodObj->surcharge_fixed_amount_tax_excl)
                ? $methodObj->surcharge_fixed_amount_tax_excl
                : '0.00',
            'percentageFee' => isset($methodObj->surcharge_percentage)
                ? $methodObj->surcharge_percentage
                : '0.00',
            'maxFeeCap' => isset($methodObj->surcharge_limit)
                ? $methodObj->surcharge_limit
                : '0.00',
        ];
    }

    /**
     * Format order restrictions
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     * @param array $method API method data
     *
     * @return array Formatted restrictions
     */
    private function formatOrderRestrictions($methodObj, array $method): array
    {
        // Get Mollie API limits (hard limits from Mollie)
        $apiMinAmount = null;
        $apiMaxAmount = null;

        if (!empty($method['minimumAmount']) && is_array($method['minimumAmount']) && isset($method['minimumAmount']['value'])) {
            $apiMinAmount = $method['minimumAmount']['value'];
        }

        if (!empty($method['maximumAmount']) && is_array($method['maximumAmount']) && isset($method['maximumAmount']['value'])) {
            $apiMaxAmount = $method['maximumAmount']['value'];
        }

        // Get user-configured values from database (can override within API limits)
        $minAmount = (isset($methodObj->min_amount) && $methodObj->min_amount > 0)
            ? (string) $methodObj->min_amount
            : '';
        $maxAmount = (isset($methodObj->max_amount) && $methodObj->max_amount > 0)
            ? (string) $methodObj->max_amount
            : '';

        return [
            'minAmount' => $minAmount,
            'maxAmount' => $maxAmount,
            // Include API limits for validation and display as helper text
            'apiMinAmount' => $apiMinAmount,
            'apiMaxAmount' => $apiMaxAmount,
        ];
    }

    /**
     * Format Apple Pay settings
     *
     * @return array Apple Pay settings
     */
    private function formatApplePaySettings(): array
    {
        return [
            'directProduct' => (bool) ($this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT) ?: 0),
            'directCart' => (bool) ($this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_CART) ?: 0),
            'buttonStyle' => (int) ($this->configuration->get(Config::MOLLIE_APPLE_PAY_DIRECT_STYLE) ?: 0),
        ];
    }

    /**
     * Get payment method title from translations or fallback to API name
     *
     * @param string $methodId Method ID
     * @param string $defaultName Default name from API
     *
     * @return string Payment method title
     */
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

    /**
     * Get credit card Mollie Components setting based on environment
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     *
     * @return bool Mollie Components enabled
     */
    private function getCreditCardMollieComponentsSetting($methodObj): bool
    {
        $environment = isset($methodObj->live_environment) ? (int) $methodObj->live_environment : 0;
        $currentEnv = $environment ? 'production' : 'sandbox';
        $configKey = Config::MOLLIE_IFRAME[$currentEnv];
        $rawValue = $this->configuration->get($configKey);
        $value = (bool) ($rawValue ?: 0);

        $this->logger->info('LOAD mollieComponents', [
            'environment' => $currentEnv,
            'config_key' => $configKey,
            'raw_value' => $rawValue,
            'bool_value' => $value,
            'methodObj_live_env' => $methodObj->live_environment ?? 'null',
        ]);

        return $value;
    }

    /**
     * Get credit card one-click payment setting based on environment
     *
     * @param \MolPaymentMethod $methodObj Payment method object
     *
     * @return bool One-click payments enabled
     */
    private function getCreditCardOneClickSetting($methodObj): bool
    {
        $environment = isset($methodObj->live_environment) ? (int) $methodObj->live_environment : 0;
        $currentEnv = $environment ? 'production' : 'sandbox';
        $configKey = Config::MOLLIE_SINGLE_CLICK_PAYMENT[$currentEnv];
        $rawValue = $this->configuration->get($configKey);
        $value = (bool) ($rawValue ?: 0);

        $this->logger->info('LOAD oneClickPayments', [
            'environment' => $currentEnv,
            'config_key' => $configKey,
            'raw_value' => $rawValue,
            'bool_value' => $value,
            'methodObj_live_env' => $methodObj->live_environment ?? 'null',
        ]);

        return $value;
    }
}
