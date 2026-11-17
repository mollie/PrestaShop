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

use Mollie\Config\Config;
use Mollie\Controller\AbstractMollieController;
use Mollie\DTO\Express\ExpressSubmitCheck;
use Mollie\Infrastructure\Response\JsonResponse;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressCartCarrierService;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Service\ExpressComponent\ExpressSessionService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Serves the Express Component: hands the browser a Checkout Session token for the current cart
 * total, and answers the component's submit event right before Mollie creates the payment. On the
 * payment step the cart is complete; on the cart page and the top of the checkout Mollie collects
 * the customer, addresses and carrier, so the visitor may still be anonymous.
 */
class MollieExpressComponentAjaxModuleFrontController extends AbstractMollieController
{
    const FILE_NAME = 'expressComponentAjax';

    const ACTION_CREATE_SESSION = 'mollie_express_create_session';
    const ACTION_VALIDATE_CART = 'mollie_express_validate_cart';

    const ERROR_NOT_AVAILABLE = 'not_available';
    const ERROR_ORDER_EXISTS = 'order_exists';
    const ERROR_FORBIDDEN = 'forbidden';
    const ERROR_API = 'api_error';
    const ERROR_CART_BUSY = 'cart_busy';
    const ERROR_UNKNOWN_PLACEMENT = 'unknown_placement';

    /** @var bool */
    public $ssl = true;

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $placement = (string) Tools::getValue('placement', Config::EXPRESS_PLACEMENT_PAYMENT);

        if (!isset(Config::EXPRESS_PLACEMENT_FLAGS[$placement])) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_UNKNOWN_PLACEMENT, JsonResponse::HTTP_BAD_REQUEST));
        }

        $collectDetails = Config::EXPRESS_PLACEMENT_PAYMENT !== $placement;

        if (!$this->isCartOwnedByVisitor($this->allowsAnonymousVisitor($collectDetails))) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_FORBIDDEN, JsonResponse::HTTP_FORBIDDEN));
        }

        /** @var ExpressComponentAvailabilityService $availability */
        $availability = $this->module->getService(ExpressComponentAvailabilityService::class);

        if (!$availability->isPlacementEnabled($placement)) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_NOT_AVAILABLE, JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
        }

        $isAvailable = $collectDetails
            ? $availability->isAvailableForCollect($this->context->cart)
            : $this->isAvailableOnPaymentStep($availability, $logger);

        if (!$isAvailable) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_NOT_AVAILABLE, JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
        }

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->module->getService(OrderRepositoryInterface::class);

        if ($orderRepository->getOrderIdByCartId((int) $this->context->cart->id) > 0) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_ORDER_EXISTS, JsonResponse::HTTP_CONFLICT));
        }

        $lockResult = $this->applyLock(sprintf('%s-%d', self::FILE_NAME, (int) $this->context->cart->id));

        if (!$lockResult->isSuccessful()) {
            $this->ajaxResponse(JsonResponse::error($this->module->l('Resource conflict', self::FILE_NAME), JsonResponse::HTTP_CONFLICT));
        }

        try {
            switch (Tools::getValue('action')) {
                case self::ACTION_CREATE_SESSION:
                    $response = $this->createSession($availability, $collectDetails);
                    break;
                case self::ACTION_VALIDATE_CART:
                    $response = $this->validateCart($placement);
                    break;
                default:
                    $response = JsonResponse::error('unknown_action', JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Express Component request failed', self::FILE_NAME), [
                'action' => Tools::getValue('action'),
                'cart_id' => (int) $this->context->cart->id,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $response = JsonResponse::error(self::ERROR_API, JsonResponse::HTTP_BAD_GATEWAY);
        } finally {
            $this->releaseLock();
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        $this->ajaxResponse($response);
    }

    private function isAvailableOnPaymentStep(ExpressComponentAvailabilityService $availability, LoggerInterface $logger): bool
    {
        /** @var ExpressCartCarrierService $cartCarrierService */
        $cartCarrierService = $this->module->getService(ExpressCartCarrierService::class);

        // A one page checkout request may be rewriting this cart right now; the script asks again.
        if ($cartCarrierService->isCartBeingRewritten($this->context->cart)) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_CART_BUSY, JsonResponse::HTTP_CONFLICT));
        }

        try {
            $cartCarrierService->restoreCarrierChoice($this->context->cart);

            return $availability->isAvailable($this->context->cart);
        } catch (\PrestaShopException $exception) {
            // The temporary address this request loaded was deleted underneath it.
            $logger->debug(sprintf('%s - Cart changed while it was read', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $this->ajaxResponse(JsonResponse::error(self::ERROR_CART_BUSY, JsonResponse::HTTP_CONFLICT));
        }

        return false;
    }

    private function createSession(ExpressComponentAvailabilityService $availability, bool $collectDetails): JsonResponse
    {
        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        $session = $sessionService->getOrCreateSession($this->context->cart, false, $collectDetails);

        if ($collectDetails) {
            // expressReturn lets an anonymous visitor in only from the browser that started the session.
            $this->context->cookie->__set(Config::EXPRESS_CART_COOKIE, (int) $this->context->cart->id);
            $this->context->cookie->write();
        }

        return JsonResponse::success([
            'sessionId' => $session->getId(),
            'clientAccessToken' => $session->getClientAccessToken(),
            'amount' => [
                'currency' => $session->getAmountCurrency(),
                'value' => $session->getAmountValue(),
            ],
            'buttons' => $availability->getButtonVisibility(),
        ]);
    }

    private function validateCart(string $placement): JsonResponse
    {
        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        // expressReturn sends a cancelled or failed payment back to the strip it was started from.
        $this->context->cookie->__set(Config::EXPRESS_PLACEMENT_COOKIE, $placement);
        $this->context->cookie->write();

        /** @var ExpressSubmitCheck $check */
        $check = $sessionService->validateBeforeSubmit($this->context->cart, (string) Tools::getValue('sessionId'));

        return JsonResponse::success($check->jsonSerialize());
    }

    /**
     * The session is always built for the visitor's own cart; a cart id from the request is never
     * accepted. Guests have a customer record too, so the check is ownership, not login state.
     * An anonymous visitor owns a cart without a customer, which only a collect session accepts.
     */
    private function isCartOwnedByVisitor(bool $allowAnonymous): bool
    {
        $customerId = (int) ($this->context->customer->id ?? 0);
        $cart = $this->context->cart;

        return ($customerId > 0 || $allowAnonymous)
            && (int) $cart->id > 0
            && (int) $cart->id_customer === $customerId;
    }

    /**
     * A session is created in the mode of the placement asking for it; the submit check pays the
     * session already stored for the cart, so that row decides.
     */
    private function allowsAnonymousVisitor(bool $collectDetails): bool
    {
        if (self::ACTION_VALIDATE_CART !== Tools::getValue('action')) {
            return $collectDetails;
        }

        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        $stored = $sessionService->findStoredSession($this->context->cart);

        return $stored && (bool) $stored->collect_details;
    }
}
