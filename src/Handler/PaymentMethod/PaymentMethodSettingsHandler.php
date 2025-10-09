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

namespace Mollie\Handler\PaymentMethod;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Exception\MollieException;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\CountryRepository;
use Mollie\Repository\CustomerRepository;
use Mollie\Repository\PaymentMethodLangRepositoryInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiService;
use Mollie\Utility\ExceptionUtility;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMethodSettingsHandler
{
    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var PaymentMethodLangRepositoryInterface */
    private $paymentMethodLangRepository;

    /** @var CountryRepository */
    private $countryRepository;

    /** @var CustomerRepository */
    private $customerRepository;

    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var LoggerInterface */
    private $logger;

    /** @var ApiService */
    private $apiService;

    /** @var \Mollie */
    private $module;

    public function __construct(
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        PaymentMethodLangRepositoryInterface $paymentMethodLangRepository,
        CountryRepository $countryRepository,
        CustomerRepository $customerRepository,
        ConfigurationAdapter $configuration,
        LoggerInterface $logger,
        ApiService $apiService,
        \Mollie $module
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodLangRepository = $paymentMethodLangRepository;
        $this->countryRepository = $countryRepository;
        $this->customerRepository = $customerRepository;
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->apiService = $apiService;
        $this->module = $module;
    }

    /**
     * Handle payment method settings save
     *
     * @param string $methodId Payment method ID
     * @param array $settings Settings data from frontend
     * @param int $environment Environment (0 = test, 1 = live)
     * @param int $shopId Shop ID
     *
     * @throws MollieException
     */
    public function handlePaymentMethodSave(string $methodId, array $settings, int $environment, int $shopId): void
    {
        // Get or create payment method
        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId(
            $methodId,
            $environment,
            $shopId
        );

        $paymentMethod = new MolPaymentMethod();
        if ($paymentMethodId) {
            $paymentMethod = new MolPaymentMethod((int) $paymentMethodId);
        }

        // Fetch fresh data from Mollie API (same as old SettingsSaveService behavior)
        // This ensures images_json and other API data is always up-to-date
        $apiMethodData = $this->fetchMethodFromApi($methodId);

        // Handle basic settings
        $this->handleBasicSettings($paymentMethod, $methodId, $settings, $environment, $shopId, $apiMethodData);

        // Handle payment fees
        if (isset($settings['paymentFees'])) {
            $this->handlePaymentFees($paymentMethod, $settings['paymentFees']);
        }

        // Handle payment restrictions (before save to set flag)
        if (isset($settings['paymentRestrictions'])) {
            $this->handlePaymentRestrictionsFlag($paymentMethod, $settings['paymentRestrictions']);
        }

        // Save payment method (creates record and populates ID)
        if (!$paymentMethod->save()) {
            throw new MollieException('Failed to save payment method');
        }

        // Handle country and customer group restrictions (requires saved ID)
        if (isset($settings['paymentRestrictions'])) {
            $this->handlePaymentRestrictions($paymentMethod, $settings['paymentRestrictions']);
        }

        // Handle title translations
        if (isset($settings['title']) && !empty($settings['title'])) {
            $this->handleTitleTranslations($methodId, $settings['title'], $shopId);
        }

        // Handle method-specific settings
        if ($methodId === 'creditcard') {
            $this->handleCreditCardSettings($settings, $environment);
        }

        if ($methodId === 'applepay' && isset($settings['applePaySettings'])) {
            $this->handleApplePaySettings($settings['applePaySettings']);
        }
    }

    /**
     * Handle basic payment method settings
     *
     * @param MolPaymentMethod $paymentMethod Payment method object
     * @param string $methodId Method ID
     * @param array $settings Settings data
     * @param int $environment Environment
     * @param int $shopId Shop ID
     * @param array|null $apiMethodData Data from Mollie API
     */
    private function handleBasicSettings(
        MolPaymentMethod $paymentMethod,
        string $methodId,
        array $settings,
        int $environment,
        int $shopId,
        ?array $apiMethodData = null
    ): void {
        $paymentMethod->id_method = $methodId;
        $paymentMethod->method_name = $methodId;
        $paymentMethod->enabled = $settings['enabled'];
        $paymentMethod->method = $settings['apiSelection'] ?? 'payments';
        $paymentMethod->description = $settings['transactionDescription'] ?? '';

        // Save min/max amounts - user can override API defaults within API limits
        // Empty values default to 0 and will use API limits in validation
        $paymentMethod->min_amount = (float) ($settings['orderRestrictions']['minAmount'] ?? 0);
        $paymentMethod->max_amount = (float) ($settings['orderRestrictions']['maxAmount'] ?? 0);

        // Save image from API (matching old SettingsSaveService behavior)
        // This keeps images_json up-to-date with Mollie API
        if ($apiMethodData && isset($apiMethodData['image'])) {
            $paymentMethod->images_json = json_encode($apiMethodData['image']);
        } elseif (!$paymentMethod->images_json) {
            // If no API data and no existing image, set empty array to avoid NULL
            $paymentMethod->images_json = json_encode([]);
        }

        $paymentMethod->live_environment = $environment ? true : false;
        $paymentMethod->id_shop = $shopId;
    }

    /**
     * Handle payment fees settings
     *
     * @param MolPaymentMethod $paymentMethod Payment method object
     * @param array $paymentFees Payment fees data
     *
     * @throws MollieException
     */
    private function handlePaymentFees(MolPaymentMethod $paymentMethod, array $paymentFees): void
    {
        $feeType = 0;

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

        // Validate surcharge percentage
        if ($feeType === 2 || $feeType === 3) {
            $surchargePercentage = (float) ($paymentFees['percentageFee'] ?? 0);
            if ($surchargePercentage <= -100 || $surchargePercentage >= 100) {
                throw new MollieException('Surcharge percentage must be between -100% and 100%');
            }
        }

        $paymentMethod->surcharge = $feeType;
        $paymentMethod->surcharge_fixed_amount_tax_excl = (float) ($paymentFees['fixedFeeTaxExcl'] ?? '0.00');
        $paymentMethod->surcharge_percentage = (float) ($paymentFees['percentageFee'] ?? '0.00');
        $paymentMethod->surcharge_limit = (float) ($paymentFees['maxFeeCap'] ?? '0.00');
        $paymentMethod->tax_rules_group_id = (int) ($paymentFees['taxGroup'] ?? '0');
    }

    /**
     * Set payment restrictions flag before save
     *
     * @param MolPaymentMethod $paymentMethod Payment method object
     * @param array $restrictions Restrictions data
     */
    private function handlePaymentRestrictionsFlag(MolPaymentMethod $paymentMethod, array $restrictions): void
    {
        $paymentMethod->is_countries_applicable = (bool) (($restrictions['acceptFrom'] ?? 'all') === 'selected');
    }

    /**
     * Handle payment restrictions (requires saved payment method ID)
     *
     * @param MolPaymentMethod $paymentMethod Payment method object
     * @param array $restrictions Restrictions data
     */
    private function handlePaymentRestrictions(MolPaymentMethod $paymentMethod, array $restrictions): void
    {
        $selectedCountries = [];
        $excludedCountries = [];

        if (($restrictions['acceptFrom'] ?? 'all') === 'selected' && isset($restrictions['selectedCountries'])) {
            $selectedCountries = $restrictions['selectedCountries'];
        }

        if (isset($restrictions['excludeCountries'])) {
            $excludedCountries = $restrictions['excludeCountries'];
        }

        try {
            $this->countryRepository->updatePaymentMethodCountries(
                (int) $paymentMethod->id,
                $selectedCountries
            );
            $this->countryRepository->updatePaymentMethodExcludedCountries(
                (int) $paymentMethod->id,
                $excludedCountries
            );

            if (isset($restrictions['excludeCustomerGroups'])) {
                $this->customerRepository->updatePaymentMethodExcludedCustomerGroups(
                    (int) $paymentMethod->id,
                    $restrictions['excludeCustomerGroups']
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to update payment restrictions', [
                'payment_method_id' => $paymentMethod->id,
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
            throw new MollieException('Failed to update payment restrictions');
        }
    }

    /**
     * Handle title translations for all languages
     *
     * @param string $methodId Method ID
     * @param string $title Title text
     * @param int $shopId Shop ID
     */
    private function handleTitleTranslations(string $methodId, string $title, int $shopId): void
    {
        try {
            $languages = \Language::getLanguages(false, $shopId);
            foreach ($languages as $language) {
                $this->paymentMethodLangRepository->savePaymentTitleTranslation(
                    $methodId,
                    (int) $language['id_lang'],
                    $title,
                    $shopId
                );
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to save title translations', [
                'method_id' => $methodId,
                'exception' => ExceptionUtility::getExceptions($e),
            ]);
        }
    }

    /**
     * Handle credit card specific settings
     *
     * @param array $settings Settings data
     * @param int $environment Environment
     */
    private function handleCreditCardSettings(array $settings, int $environment): void
    {
        $currentEnv = $environment ? 'production' : 'sandbox';

        if (isset($settings['mollieComponents'])) {
            $configKey = Config::MOLLIE_IFRAME[$currentEnv];
            $value = $settings['mollieComponents'] ? 1 : 0;
            $this->configuration->updateValue($configKey, $value);
        }

        if (isset($settings['oneClickPayments'])) {
            $configKey = Config::MOLLIE_SINGLE_CLICK_PAYMENT[$currentEnv];
            $value = $settings['oneClickPayments'] ? 1 : 0;
            $this->configuration->updateValue($configKey, $value);
        }

        if (isset($settings['useCustomLogo'])) {
            $this->configuration->updateValue(
                Config::MOLLIE_SHOW_CUSTOM_LOGO,
                $settings['useCustomLogo'] ? 1 : 0
            );
        }
    }

    /**
     * Handle Apple Pay specific settings
     *
     * @param array $applePaySettings Apple Pay settings
     */
    private function handleApplePaySettings(array $applePaySettings): void
    {
        $this->configuration->updateValue(
            Config::MOLLIE_APPLE_PAY_DIRECT_PRODUCT,
            $applePaySettings['directProduct'] ? 1 : 0
        );
        $this->configuration->updateValue(
            Config::MOLLIE_APPLE_PAY_DIRECT_CART,
            $applePaySettings['directCart'] ? 1 : 0
        );
        $this->configuration->updateValue(
            Config::MOLLIE_APPLE_PAY_DIRECT_STYLE,
            $applePaySettings['buttonStyle']
        );
    }

    /**
     * Fetch payment method data from Mollie API
     * Matches old SettingsSaveService behavior - always sync with API on save
     *
     * @param string $methodId Payment method ID
     *
     * @return array|null Method data from API or null if not found
     */
    private function fetchMethodFromApi(string $methodId): ?array
    {
        try {
            $mollieClient = $this->module->getApiClient();
            if (!$mollieClient) {
                $this->logger->warning('Cannot fetch payment method from API - client not configured', [
                    'method_id' => $methodId,
                ]);

                return null;
            }

            // Get all methods from Mollie API (same as old SettingsSaveService)
            $apiMethods = $this->apiService->getMethodsForConfig($mollieClient);

            // Find the specific method we're saving
            foreach ($apiMethods as $apiMethod) {
                if ($apiMethod['id'] === $methodId) {

                    return $apiMethod;
                }
            }

            $this->logger->warning('Payment method not found in Mollie API response', [
                'method_id' => $methodId,
                'available_methods' => array_column($apiMethods, 'id'),
            ]);

            return null;
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch payment method from Mollie API', [
                'method_id' => $methodId,
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            return null;
        }
    }
}
