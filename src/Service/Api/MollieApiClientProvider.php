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

namespace Mollie\Service\Api;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\MollieApiClient;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\ApiKeyService;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Central place that builds Mollie API clients.
 *
 * All API key selection lives here so that, in multistore setups and after key
 * migrations, actions on an order can be pointed at the key that owns the
 * payment instead of whatever key happens to be configured on the current shop.
 * Previously every caller resolved the key itself and called
 * `$module->getApiClient()` directly (~60 call sites), which made that
 * impossible to do consistently.
 */
class MollieApiClientProvider
{
    /** @var ConfigurationAdapter */
    private $configuration;

    /** @var ApiKeyService */
    private $apiKeyService;

    /** @var ModuleFactory */
    private $moduleFactory;

    /** @var array<string, MollieApiClient|null> clients cached per API key */
    private $clientCache = [];

    public function __construct(
        ConfigurationAdapter $configuration,
        ApiKeyService $apiKeyService,
        ModuleFactory $moduleFactory
    ) {
        $this->configuration = $configuration;
        $this->apiKeyService = $apiKeyService;
        $this->moduleFactory = $moduleFactory;
    }

    /**
     * Resolve the API key configured for a given shop (multistore aware).
     * When $shopId is null the current shop context is used.
     */
    public function resolveApiKeyForShop(?int $shopId = null): ?string
    {
        $keyConfig = $this->isLiveEnvironment()
            ? Config::MOLLIE_API_KEY
            : Config::MOLLIE_API_KEY_TEST;

        return $this->configuration->get($keyConfig, $shopId);
    }

    /**
     * Build (and cache) an API client for the key configured on the given shop.
     */
    public function getForShop(?int $shopId = null, bool $subscriptionOrder = false): ?MollieApiClient
    {
        $apiKey = $this->resolveApiKeyForShop($shopId);

        if (!$apiKey) {
            return null;
        }

        return $this->getForApiKey($apiKey, $subscriptionOrder);
    }

    /**
     * Build (and cache) an API client for an explicit API key. This is the single
     * point of client creation; key-selection strategies (per shop, per order,
     * fallback) resolve to a key and then call through here.
     */
    public function getForApiKey(string $apiKey, bool $subscriptionOrder = false): ?MollieApiClient
    {
        $cacheKey = md5($apiKey) . ($subscriptionOrder ? ':sub' : '');

        if (array_key_exists($cacheKey, $this->clientCache)) {
            return $this->clientCache[$cacheKey];
        }

        $moduleVersion = (string) $this->moduleFactory->getModuleVersion();

        return $this->clientCache[$cacheKey] = $this->apiKeyService->setApiKey(
            $apiKey,
            $moduleVersion,
            $subscriptionOrder,
            $this->getEnvironment()
        );
    }

    private function getEnvironment(): int
    {
        return (int) $this->configuration->get(Config::MOLLIE_ENVIRONMENT);
    }

    private function isLiveEnvironment(): bool
    {
        return Config::ENVIRONMENT_LIVE === $this->getEnvironment();
    }
}
