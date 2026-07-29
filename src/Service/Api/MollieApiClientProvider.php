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
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ApiKeyService;
use Mollie\Utility\TransactionUtility;

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
     * Build the API client to use for an existing transaction.
     *
     * Considers the keys that are currently configured and could own the
     * payment - the order shop's key and the manually-entered fallback key -
     * and returns the first one that can actually access the transaction on
     * Mollie. When the order carries a stored key reference, the configured key
     * whose fingerprint matches it is tried first (it is the key that created
     * the order, so it is the most likely to succeed and avoids a wasted probe).
     *
     * Note: the stored reference is a one-way fingerprint, so it cannot recover
     * a key that is no longer configured anywhere. Recovering access to orders
     * created with a rotated/removed key therefore requires that key to be
     * present as the shop key or the fallback key. This keeps refunds/captures/
     * webhooks working in multistore and after a key migration, as long as the
     * owning key is still configured in one of those slots.
     *
     * When no candidate can be verified (e.g. transaction not found on any key),
     * the first usable client is returned so behaviour degrades to the previous
     * single-key attempt rather than failing to build a client at all.
     */
    public function getForTransaction(string $transactionId, bool $subscriptionOrder = false): ?MollieApiClient
    {
        $payment = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transactionId);
        $payment = is_array($payment) ? $payment : [];

        $shopId = $this->resolveShopIdFromPayment($payment);
        $storedRef = !empty($payment['api_key_ref']) ? (string) $payment['api_key_ref'] : null;

        $candidateKeys = $this->buildCandidateKeys($shopId, $storedRef);

        if (empty($candidateKeys)) {
            return $this->getForShop($shopId, $subscriptionOrder);
        }

        // With a single candidate there is no choice to make: return it directly
        // and skip the verification round-trip (the common single-store case).
        if (1 === count($candidateKeys)) {
            return $this->getForApiKey($candidateKeys[0], $subscriptionOrder);
        }

        // If the order carries a stored key reference and the top candidate's
        // fingerprint matches it, that key created the order: trust it and skip
        // the verification round-trip (buildCandidateKeys puts the matching key
        // first). Only fall through to probing when the owning key is unknown
        // (no stored ref, e.g. legacy orders) or no longer configured.
        if (null !== $storedRef && $this->getApiKeyReference($candidateKeys[0]) === $storedRef) {
            return $this->getForApiKey($candidateKeys[0], $subscriptionOrder);
        }

        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);
        $firstUsableClient = null;

        foreach ($candidateKeys as $apiKey) {
            $client = $this->getForApiKey($apiKey, $subscriptionOrder);

            if (!$client) {
                continue;
            }

            if (null === $firstUsableClient) {
                $firstUsableClient = $client;
            }

            if ($this->clientCanAccessTransaction($client, $transactionId, $isOrderTransaction)) {
                return $client;
            }
        }

        return $firstUsableClient;
    }

    /**
     * Ordered, de-duplicated list of API keys to try for a transaction: the key
     * matching the order's stored reference first (its original key), then the
     * order shop's current key, then the manually-entered fallback key.
     *
     * @return string[]
     */
    private function buildCandidateKeys(?int $shopId, ?string $storedRef): array
    {
        $keys = [];

        foreach ([$this->resolveApiKeyForShop($shopId), $this->getConfiguredFallbackApiKey($shopId)] as $key) {
            if ($key && !in_array($key, $keys, true)) {
                $keys[] = $key;
            }
        }

        // Put the key whose fingerprint matches the order's stored reference
        // first. A partition (not usort) keeps this deterministic and preserves
        // the shop-key-before-fallback order among the rest - PHP < 8.0 sort is
        // not stable, so an equal-comparator usort could otherwise reorder them.
        if (null !== $storedRef) {
            $matching = [];
            $rest = [];
            foreach ($keys as $key) {
                if ($this->getApiKeyReference($key) === $storedRef) {
                    $matching[] = $key;
                } else {
                    $rest[] = $key;
                }
            }
            $keys = array_merge($matching, $rest);
        }

        return $keys;
    }

    /**
     * Whether a client can fetch the given transaction (i.e. its key owns it).
     */
    private function clientCanAccessTransaction(MollieApiClient $client, string $transactionId, bool $isOrderTransaction): bool
    {
        try {
            if ($isOrderTransaction) {
                $client->orders->get($transactionId);
            } else {
                $client->payments->get($transactionId);
            }

            return true;
        } catch (ApiException $e) {
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * The manually-entered fallback key, or null. A single key is used
     * regardless of environment - whichever key is entered is used.
     */
    public function getConfiguredFallbackApiKey(?int $shopId = null): ?string
    {
        $key = $this->configuration->get(Config::MOLLIE_API_KEY_FALLBACK, $shopId);

        return $key ?: null;
    }

    /**
     * Find which shop created a transaction, from its stored payment row.
     */
    private function resolveShopIdFromPayment(array $payment): ?int
    {
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
