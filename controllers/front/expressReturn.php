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

use Mollie\Adapter\API\MollieSessionApiAdapterInterface;
use Mollie\Controller\AbstractMollieController;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressSessionService;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\MollieStatusUtility;
use Mollie\Validator\OrderCallBackValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Where Mollie sends the shopper after an Express Component payment. The webhook is the only
 * place that creates the order, so this page waits for it: return.php cannot be reused because
 * it bounces the shopper to the cart before the webhook has written the mollie_payments row.
 */
class MollieExpressReturnModuleFrontController extends AbstractMollieController
{
    const FILE_NAME = 'expressReturn';

    /** The wait template polls 10 times at 3 seconds; a completed session earns a few more rounds. */
    const MAX_WAIT_ROUNDS = 3;

    const STATUS_WAITING = 1;
    const STATUS_REDIRECT = 2;

    /** @var bool */
    public $ssl = true;

    public function initContent()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $cartId = (int) Tools::getValue('cart_id');
        $key = (string) Tools::getValue('key');

        /** @var OrderCallBackValidator $orderCallBackValidator */
        $orderCallBackValidator = $this->module->getService(OrderCallBackValidator::class);

        if (!$cartId || !$orderCallBackValidator->validate($key, $cartId)) {
            Tools::redirect('index.php');
        }

        $cart = new Cart($cartId);

        if ((int) $cart->id_customer !== (int) $this->context->customer->id) {
            Tools::redirect('index.php');
        }

        $orderId = (int) Order::getIdByCartId($cartId);

        // A status poll must get JSON even once the order exists: the wait template cannot read a
        // redirect, kept polling and only reached the confirmation page after its last round.
        if ($orderId && (!Tools::getValue('ajax') || Tools::getValue('failed'))) {
            $this->redirectToOrderConfirmation($cart, $orderId);
        }

        // The wait template gives up by reloading the status endpoint with failed=1, so this
        // must come before the ajax branch or the shopper lands on raw JSON.
        if (Tools::getValue('failed')) {
            $this->handleWaitTimeout($cart, $key);
        }

        if (Tools::getValue('ajax')) {
            $this->processGetStatus($cart);
            exit;
        }

        if ($this->hasFailedPaymentWithoutOrder($cart)) {
            $this->redirectToPaymentStep($this->module->l('Your payment was not successful. Try again.', self::FILE_NAME));
        }

        parent::initContent();

        $this->context->smarty->assign([
            'checkStatusEndpoint' => $this->context->link->getModuleLink(
                $this->module->name,
                self::FILE_NAME,
                [
                    'ajax' => 1,
                    'action' => 'getStatus',
                    'cart_id' => $cartId,
                    'key' => $key,
                    'round' => (int) Tools::getValue('round'),
                ],
                true
            ),
            'link' => $this->context->link,
        ]);

        $this->setTemplate('module:mollie/views/templates/front/17_mollie_wait.tpl');

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));
    }

    private function processGetStatus(Cart $cart): void
    {
        header('Content-Type: application/json;charset=UTF-8');

        $orderId = (int) Order::getIdByCartId((int) $cart->id);

        if ($orderId) {
            exit(json_encode([
                'success' => true,
                'status' => self::STATUS_REDIRECT,
                'href' => $this->getOrderConfirmationUrl($cart, $orderId),
            ]));
        }

        if ($this->hasFailedPaymentWithoutOrder($cart)) {
            $this->setWarning($this->module->l('Your payment was not successful. Try again.', self::FILE_NAME));

            exit(json_encode([
                'success' => true,
                'status' => self::STATUS_REDIRECT,
                'href' => $this->getPaymentStepUrl(),
            ]));
        }

        exit(json_encode([
            'success' => true,
            'status' => self::STATUS_WAITING,
        ]));
    }

    /**
     * The wait page gave up. A session Mollie reports as completed means the payment exists and
     * the webhook is late, so the shopper gets another round instead of a failure message.
     */
    private function handleWaitTimeout(Cart $cart, string $key): void
    {
        $round = (int) Tools::getValue('round');

        if ($round < self::MAX_WAIT_ROUNDS && $this->isSessionCompleted($cart)) {
            Tools::redirect($this->context->link->getModuleLink(
                $this->module->name,
                self::FILE_NAME,
                [
                    'cart_id' => (int) $cart->id,
                    'key' => $key,
                    'round' => $round + 1,
                ],
                true
            ));
        }

        $this->redirectToPaymentStep($this->module->l('We have not received the payment confirmation yet. If you were charged, your order will appear once the confirmation arrives.', self::FILE_NAME));
    }

    private function isSessionCompleted(Cart $cart): bool
    {
        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        $stored = $sessionService->findStoredSession($cart);

        if (!$stored) {
            return false;
        }

        try {
            /** @var MollieSessionApiAdapterInterface $sessionApi */
            $sessionApi = $this->module->getService(MollieSessionApiAdapterInterface::class);

            return $sessionApi->get((string) $stored->session_id)->isCompleted();
        } catch (\Throwable $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);

            $logger->warning(sprintf('%s - Could not read the express session while waiting for the webhook', self::FILE_NAME), [
                'cart_id' => (int) $cart->id,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            return false;
        }
    }

    /**
     * Only a failed attempt made for the current express session counts. The newest
     * mollie_payments row of the cart may be an older attempt by another method, cancelled
     * before the shopper turned to the express component. The payment carries the session's
     * description as its order reference, so the two are matched by that, not by time: the row
     * is stamped by the database clock and the session by PHP's, and on many shops those differ
     * by the time zone offset.
     */
    private function hasFailedPaymentWithoutOrder(Cart $cart): bool
    {
        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->module->getService(PaymentMethodRepositoryInterface::class);

        $payment = $paymentMethodRepository->getPaymentBy('cart_id', (int) $cart->id);

        if (!is_array($payment) || !isset($payment['bank_status'])) {
            return false;
        }

        if (!MollieStatusUtility::isPaymentFailed($payment['bank_status']) || (int) ($payment['order_id'] ?? 0) > 0) {
            return false;
        }

        /** @var ExpressSessionService $sessionService */
        $sessionService = $this->module->getService(ExpressSessionService::class);

        $stored = $sessionService->findStoredSession($cart);

        if (!$stored || empty($payment['order_reference'])) {
            return false;
        }

        try {
            /** @var MollieSessionApiAdapterInterface $sessionApi */
            $sessionApi = $this->module->getService(MollieSessionApiAdapterInterface::class);

            $description = $sessionApi->get((string) $stored->session_id)->getDescription();
        } catch (\Throwable $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);

            $logger->warning(sprintf('%s - Could not read the express session to match a failed payment', self::FILE_NAME), [
                'cart_id' => (int) $cart->id,
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            return false;
        }

        return '' !== $description && hash_equals($description, (string) $payment['order_reference']);
    }

    private function redirectToOrderConfirmation(Cart $cart, int $orderId): void
    {
        Tools::redirect($this->getOrderConfirmationUrl($cart, $orderId));
    }

    private function getOrderConfirmationUrl(Cart $cart, int $orderId): string
    {
        return $this->context->link->getPageLink(
            'order-confirmation',
            true,
            null,
            [
                'id_cart' => (int) $cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => $orderId,
                'key' => $cart->secure_key,
            ]
        );
    }

    private function redirectToPaymentStep(string $message): void
    {
        $this->setWarning($message);

        Tools::redirect($this->getPaymentStepUrl());
    }

    private function getPaymentStepUrl(): string
    {
        return $this->context->link->getPageLink('order', true, $this->context->language->id, ['step' => 'payment']);
    }

    /** Read back by ErrorDisplayService::showCookieError on the cart and order pages. */
    private function setWarning(string $message): void
    {
        $this->context->cookie->__set('mollie_payment_canceled_error', json_encode([$message]));
    }
}
