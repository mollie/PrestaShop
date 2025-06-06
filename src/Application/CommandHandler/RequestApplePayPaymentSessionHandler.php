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
use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Application\Command\RequestApplePayPaymentSession;
use Mollie\Exception\MollieApiException;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\ApiServiceInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Logger\LoggerInterface;

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

    public function __construct(ModuleFactory $module, ApiServiceInterface $apiService)
    {
        $this->module = $module->getModule();
        $this->apiService = $apiService;
    }

    public function handle(RequestApplePayPaymentSession $command): array
    {
        try {
            $response = $this->apiService->requestApplePayPaymentSession($this->module->getApiClient(), $command->getValidationUrl());
        } catch (MollieApiException $e) {
            /* Message is only displayed in console */
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (ApiException $e) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            
            $logger->error('Error requesting Apple Pay payment session', [
                'message' => $e->getMessage(),
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

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
