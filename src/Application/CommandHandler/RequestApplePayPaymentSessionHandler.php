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

namespace Mollie\Application\CommandHandler;

use Cart;
use Configuration;
use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Application\Command\RequestApplePayPaymentSession;
use Mollie\Config\Config;
use Mollie\Exception\MollieApiException;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\ApiKeyService;
use Mollie\Service\ApiServiceInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class RequestApplePayPaymentSessionHandler
{
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var ApiServiceInterface
     */
    private $apiService;
    /**
     * @var ApiKeyService
     */
    private $apiKeyService;

    public function __construct(ModuleFactory $module, ApiServiceInterface $apiService, ApiKeyService $apiKeyService)
    {
        $this->module = $module->getModule();
        $this->apiService = $apiService;
        $this->apiKeyService = $apiKeyService;
    }

    public function handle(RequestApplePayPaymentSession $command): array
    {
        try {
            $response = $this->apiService->requestApplePayPaymentSession($this->getApplePaySessionApiClient(), $command->getValidationUrl());
        } catch (MollieApiException $e) {
            /* Message is only displayed in console */
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (ApiException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }

        $cartId = $command->getCartId();
        if (!$cartId) {
            $cartId = $this->createEmptyCart($command->getCurrencyId(), $command->getLangId());
        }

        return [
            'success' => true,
            'data' => $response,
            'cartId' => $cartId,
        ];
    }

    private function getApplePaySessionApiClient(): ?MollieApiClient
    {
        if (!Config::isTestMode()) {
            return $this->module->getApiClient();
        }

        $liveApiKey = Configuration::get(Config::MOLLIE_API_KEY);

        if (!$liveApiKey) {
            return $this->module->getApiClient();
        }

        return $this->apiKeyService->setApiKey(
            $liveApiKey,
            $this->module->version,
            false,
            Config::ENVIRONMENT_LIVE
        );
    }

    private function createEmptyCart(int $currencyId, int $langId): int
    {
        $cart = new Cart();
        $cart->id_currency = $currencyId;
        $cart->id_lang = $langId;
        $cart->id_address_invoice = 1;
        $cart->save();

        return (int) $cart->id;
    }
}
