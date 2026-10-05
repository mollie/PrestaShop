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

use Mollie\Controller\AbstractMollieController;
use Mollie\DTO\Express\ExpressSubmitCheck;
use Mollie\Infrastructure\Response\JsonResponse;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Service\ExpressComponent\ExpressSessionService;
use Mollie\Utility\ExceptionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Serves the Express Component on the checkout payment step: hands the browser a Checkout Session
 * token for the current cart total, and answers the component's submit event right before Mollie
 * creates the payment.
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

    /** @var bool */
    public $ssl = true;

    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        if (!$this->isCartOwnedByVisitor()) {
            $this->ajaxResponse(JsonResponse::error(self::ERROR_FORBIDDEN, JsonResponse::HTTP_FORBIDDEN));
        }

        /** @var ExpressComponentAvailabilityService $availability */
        $availability = $this->module->getService(ExpressComponentAvailabilityService::class);

        if (!$availability->isAvailable($this->context->cart)) {
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
                    $response = $this->createSession($availability);
                    break;
                case self::ACTION_VALIDATE_CART:
                    $response = $this->validateCart();
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

    private function createSession(ExpressComponentAvailabilityService $availability): JsonResponse
    {
        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        $session = $sessionService->getOrCreateSession($this->context->cart);

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

    private function validateCart(): JsonResponse
    {
        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        /** @var ExpressSubmitCheck $check */
        $check = $sessionService->validateBeforeSubmit($this->context->cart);

        return JsonResponse::success($check->jsonSerialize());
    }

    /**
     * The session is always built for the visitor's own cart; a cart id from the request is never
     * accepted. Guests have a customer record too, so the check is ownership, not login state.
     */
    private function isCartOwnedByVisitor(): bool
    {
        $customerId = (int) ($this->context->customer->id ?? 0);
        $cart = $this->context->cart;

        return $customerId > 0
            && (int) $cart->id > 0
            && (int) $cart->id_customer === $customerId;
    }
}
