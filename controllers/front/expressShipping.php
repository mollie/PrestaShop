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

use Mollie\Builder\Express\ExpressSessionRequestBuilder;
use Mollie\Controller\AbstractMollieController;
use Mollie\Exception\ExpressShippingRejectedException;
use Mollie\Infrastructure\Response\JsonResponse;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressShippingOptionsService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Mollie's shipping callback for a collect mode Express session. Mollie calls it server to server
 * (no cookies) each time the shopper picks a shipping address in the wallet sheet, and expects the
 * carriers for that address within 15 seconds.
 */
class MollieExpressShippingModuleFrontController extends AbstractMollieController
{
    const FILE_NAME = 'expressShipping';

    /** @var bool */
    public $ssl = true;
    /** @var bool */
    public $display_column_left = false;
    /** @var bool */
    public $display_column_right = false;

    /**
     * Prevent displaying the maintenance page.
     *
     * @return void
     */
    protected function displayMaintenancePage()
    {
    }

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $cartId = (int) Tools::getValue('cart_id');
        $body = json_decode((string) file_get_contents('php://input'), true);

        if (!is_array($body)) {
            $this->ajaxResponse(JsonResponse::error('invalid_request', JsonResponse::HTTP_BAD_REQUEST));
        }

        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart) || (int) $cart->id_shop !== (int) $this->context->shop->id) {
            $logger->error(sprintf('%s - Cart not found', self::FILE_NAME), ['cart_id' => $cartId]);

            $this->ajaxResponse(JsonResponse::error('cart_not_found', JsonResponse::HTTP_NOT_FOUND));
        }

        /** @var ExpressSessionRequestBuilder $requestBuilder */
        $requestBuilder = $this->module->getService(ExpressSessionRequestBuilder::class);

        if (!hash_equals($requestBuilder->getShippingCallbackToken($cart), (string) Tools::getValue('token'))) {
            $logger->error(sprintf('%s - Invalid token', self::FILE_NAME), ['cart_id' => $cartId]);

            $this->ajaxResponse(JsonResponse::error('unauthorized', JsonResponse::HTTP_UNAUTHORIZED));
        }

        /** @var MolExpressSessionRepositoryInterface $sessionRepository */
        $sessionRepository = $this->module->getService(MolExpressSessionRepositoryInterface::class);
        $session = $sessionRepository->findLatestByCartId($cartId);

        if (!$session || !(int) $session->collect_details || !hash_equals((string) $session->session_id, (string) ($body['sessionId'] ?? ''))) {
            $logger->error(sprintf('%s - Session does not match the cart', self::FILE_NAME), [
                'cart_id' => $cartId,
                'session_id' => (string) ($body['sessionId'] ?? ''),
            ]);

            $this->ajaxResponse(JsonResponse::error('forbidden', JsonResponse::HTTP_FORBIDDEN));
        }

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->module->getService(OrderRepositoryInterface::class);

        if ($orderRepository->getOrderIdByCartId($cartId) > 0) {
            $logger->error(sprintf('%s - Order already exists for the cart', self::FILE_NAME), ['cart_id' => $cartId]);

            $this->ajaxResponse(JsonResponse::error('order_exists', JsonResponse::HTTP_CONFLICT));
        }

        $lockResult = $this->applyLock(sprintf('%s-%d', self::FILE_NAME, $cartId));

        if (!$lockResult->isSuccessful()) {
            $this->ajaxResponse(JsonResponse::error($this->module->l('Resource conflict', self::FILE_NAME), JsonResponse::HTTP_CONFLICT));
        }

        $shippingAddress = is_array($body['shippingAddress'] ?? null) ? $body['shippingAddress'] : [];

        // Wallets shorten the address before payment in their own ways; the debug log shows what arrived.
        $logger->debug(sprintf('%s - Shipping address received', self::FILE_NAME), [
            'cart_id' => $cartId,
            'country' => (string) ($shippingAddress['country'] ?? ''),
            'postal_code' => (string) ($shippingAddress['postalCode'] ?? ''),
            'has_city' => '' !== trim((string) ($shippingAddress['city'] ?? '')),
            'has_region' => '' !== trim((string) ($shippingAddress['region'] ?? '')),
        ]);

        try {
            $this->setContext($cart);

            /** @var ExpressShippingOptionsService $shippingOptionsService */
            $shippingOptionsService = $this->module->getService(ExpressShippingOptionsService::class);

            $response = new JsonResponse(['options' => $shippingOptionsService->getOptions($cart, $shippingAddress)]);
        } catch (ExpressShippingRejectedException $exception) {
            $logger->info(sprintf('%s - Shipping address rejected', self::FILE_NAME), [
                'cart_id' => $cartId,
                'type' => $exception->getType(),
                'detail' => $exception->getMessage(),
                'country' => (string) ($shippingAddress['country'] ?? ''),
            ]);

            $response = $this->reject($exception->getType(), $exception->getMessage());
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Failed to list shipping options', self::FILE_NAME), [
                'cart_id' => $cartId,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            // the shopper sees "cannot ship here" instead of a broken sheet
            $response = $this->reject(ExpressShippingRejectedException::UNSUPPORTED_ADDRESS, 'The shop could not list shipping options');
        } finally {
            $this->releaseLock();
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        header('Content-Type: application/json;charset=UTF-8');

        $this->ajaxResponse($response);
    }

    private function reject(string $type, string $detail): JsonResponse
    {
        return new JsonResponse([
            'type' => $type,
            'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            'detail' => $detail,
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Mollie sends no cookie, so the cart's own currency, customer and language price the cart.
     * The country follows the placeholder address the service puts on the cart.
     */
    private function setContext(Cart $cart): void
    {
        $this->context->cart = $cart;
        $this->context->currency = new Currency((int) $cart->id_currency);
        $this->context->customer = new Customer((int) $cart->id_customer);

        $language = new Language((int) $cart->id_lang);

        if (Validate::isLoadedObject($language)) {
            $this->context->language = $language;
        }
    }
}
