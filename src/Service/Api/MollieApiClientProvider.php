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
use Mollie\Repository\PaymentMethodRepositoryInterface;
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

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var array<string, MollieApiClient|null> clients cached per API key */
    private $clientCache = [];

    public function __construct(
        ConfigurationAdapter $configuration,
        ApiKeyService $apiKeyService,
        ModuleFactory $moduleFactory,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->configuration = $configuration;
        $this->apiKeyService = $apiKeyService;
        $this->moduleFactory = $moduleFactory;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * Build the API client to use for an existing transaction. Resolves the
     * shop that owns the payment (multistore) and uses that shop's key, instead
     * of relying on whatever shop happens to be the current context. Falls back
     * to the current shop when the transaction cannot be located.
     */
    public function getForTransaction(string $transactionId, bool $subscriptionOrder = false): ?MollieApiClient
    {
        return $this->getForShop($this->resolveShopIdForTransaction($transactionId), $subscriptionOrder);
    }

    /**
     * Find which shop created a transaction, via its stored payment row.
     */
    private function resolveShopIdForTransaction(string $transactionId): ?int
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transactionId);

        if (empty($payment)) {
            return null;
        }

        if (!empty($payment['order_id'])) {
            $order = new \Order((int) $payment['order_id']);
            if (\Validate::isLoadedObject($order)) {
                return (int) $order->id_shop;
            }
        }

        if (!empty($payment['cart_id'])) {
            $cart = new \Cart((int) $payment['cart_id']);
            if (\Validate::isLoadedObject($cart)) {
                return (int) $cart->id_shop;
            }
        }

        return null;
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

    /**
     * A stable, non-reversible reference for an API key. Stored against an order
     * so we can later tell which of the known keys (order shop, current shop,
     * configured fallback) created it, without ever persisting the secret itself.
     */
    public function getApiKeyReference(string $apiKey): string
    {
        return substr(hash('sha256', $apiKey), 0, 16);
    }

    /**
     * The reference of the key currently configured on the given shop, or null
     * when no key is configured.
     */
    public function resolveApiKeyRefForShop(?int $shopId = null): ?string
    {
        $apiKey = $this->resolveApiKeyForShop($shopId);

        if (!$apiKey) {
            return null;
        }

        return $this->getApiKeyReference($apiKey);
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
