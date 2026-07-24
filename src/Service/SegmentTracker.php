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

namespace Mollie\Service;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SegmentTracker
{
    /** @var \Mollie */
    private $module;
    /** @var SegmentDataProvider */
    private $dataProvider;
    /** @var ConfigurationAdapter */
    private $configuration;
    /** @var string */
    private $apiKey;

    public function __construct(
        ModuleFactory $moduleFactory,
        SegmentDataProvider $dataProvider,
        ConfigurationAdapter $configuration
    ) {
        $this->module = $moduleFactory->getModule();
        $this->dataProvider = $dataProvider;
        $this->configuration = $configuration;
        $this->apiKey = $_ENV['SEGMENT_API_KEY'] ?? '';
    }

    public function trackModuleInstalled(string $installMethod): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'install_method' => $installMethod,
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Module Installed', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackModuleUninstalled(): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'days_since_install' => $this->dataProvider->getDaysSinceInstall(),
                'had_successful_payment' => $this->dataProvider->hadSuccessfulPayment(),
                'enabled_methods_count' => $this->dataProvider->getEnabledMethodsCount(),
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Module Uninstalled', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackModuleEnabled(): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Module Enabled', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackModuleDisabled(): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'enabled_methods_count' => $this->dataProvider->getEnabledMethodsCount(),
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Module Disabled', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackModuleUpgraded(string $previousVersion): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'previous_version' => $previousVersion,
                'enabled_methods_count' => $this->dataProvider->getEnabledMethodsCount(),
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Module Upgraded', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackModuleConfigured(bool $hasTestKey, bool $hasLiveKey): void
    {
        try {
            $isFirstConnection = !$this->configuration->get(Config::MOLLIE_SEGMENT_EVER_CONNECTED);

            if ($isFirstConnection) {
                $this->configuration->updateValue(Config::MOLLIE_SEGMENT_EVER_CONNECTED, 1);
            }

            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'has_test_key' => $hasTestKey,
                'has_live_key' => $hasLiveKey,
                'is_first_connection' => $isFirstConnection,
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Module Configured', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackPaymentMethodEnabled(MolPaymentMethod $paymentMethod, int $preEnableCount): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'method_id' => $paymentMethod->id_method,
                'method_name' => $paymentMethod->method_name,
                'is_first_method' => $preEnableCount === 0,
                'total_enabled_count' => $this->dataProvider->getEnabledMethodsCount(),
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Payment Method Enabled', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackPaymentMethodConfigured(MolPaymentMethod $paymentMethod, bool $hasCountryRestrictions): void
    {
        try {
            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'method_id' => $paymentMethod->id_method,
                'method_name' => $paymentMethod->method_name,
                'has_surcharge' => $paymentMethod->surcharge > 0,
                'has_country_restrictions' => $hasCountryRestrictions,
                'has_custom_description' => !empty($paymentMethod->description),
                'api_method' => $paymentMethod->method,
                'has_min_max_amount' => $paymentMethod->min_amount > 0 || $paymentMethod->max_amount > 0,
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'Payment Method Configured', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackFirstPaymentCreated(string $methodId, string $methodName, string $apiType, string $currency): void
    {
        try {
            if ($this->configuration->get(Config::MOLLIE_SEGMENT_FIRST_PAYMENT_CREATED)) {
                return;
            }

            $this->configuration->updateValue(Config::MOLLIE_SEGMENT_FIRST_PAYMENT_CREATED, time());

            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'method_id' => $methodId,
                'method_name' => $methodName,
                'api_type' => $apiType,
                'currency' => $currency,
                'days_since_install' => $this->dataProvider->getDaysSinceInstall(),
                'enabled_methods_count' => $this->dataProvider->getEnabledMethodsCount(),
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'First Payment Created', $properties);
        } catch (\Throwable $e) {
        }
    }

    public function trackFirstPaymentCompleted(string $methodId, string $methodName, string $apiType, string $currency): void
    {
        try {
            if ($this->configuration->get(Config::MOLLIE_SEGMENT_FIRST_PAYMENT_DONE)) {
                return;
            }

            $this->configuration->updateValue(Config::MOLLIE_SEGMENT_FIRST_PAYMENT_DONE, 1);

            $firstPaymentCreatedAt = (int) $this->configuration->get(Config::MOLLIE_SEGMENT_FIRST_PAYMENT_CREATED);
            $secondsToComplete = $firstPaymentCreatedAt > 0 ? max(0, time() - $firstPaymentCreatedAt) : 0;

            $properties = [
                'environment' => $this->dataProvider->getEnvironmentLabel(),
                'method_id' => $methodId,
                'method_name' => $methodName,
                'api_type' => $apiType,
                'currency' => $currency,
                'days_since_install' => $this->dataProvider->getDaysSinceInstall(),
                'seconds_to_complete' => $secondsToComplete,
            ];

            PrestashopModuleTracking::track($this->apiKey, $this->module, 'First Payment Completed', $properties);
        } catch (\Throwable $e) {
        }
    }
}
