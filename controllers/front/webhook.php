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

use Mollie\Adapter\ToolsAdapter;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Controller\AbstractMollieController;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Infrastructure\Response\JsonResponse;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\TransactionService;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieWebhookModuleFrontController extends AbstractMollieController
{
    private const FILE_NAME = 'webhook';

    /** @var Mollie */
    public $module;
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

    public function initContent(): void
    {
        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        /** @var ToolsAdapter $tools */
        $tools = $this->module->getService(ToolsAdapter::class);

        if (!$this->module->getApiClient()) {
            $logger->error(sprintf('Unauthorized in %s', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Unauthorized', self::FILE_NAME),
                HttpStatusCode::HTTP_UNAUTHORIZED
            ));
        }

        if (!$tools->getValue('security_token')) {
            $logger->debug(sprintf('%s - Missing security token', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Missing security token', self::FILE_NAME),
                HttpStatusCode::HTTP_BAD_REQUEST
            ));
        }

        $transactionId = (string) $tools->getValue('id');

        if (!$transactionId) {
            $logger->error(sprintf('%s - Missing transaction ID', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Missing transaction id', self::FILE_NAME),
                HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY
            ));
        }

        $lockResult = $this->applyLock(sprintf(
            '%s-%s',
            self::FILE_NAME,
            $tools->getValue('security_token')
        ));

        if (!$lockResult->isSuccessful()) {
            $logger->error(sprintf('%s - Resource conflict', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Resource conflict', self::FILE_NAME),
                HttpStatusCode::HTTP_CONFLICT
            ));
        }

        try {
            $this->executeWebhook($transactionId);
        } catch (ApiException $exception) {
            $this->handleException($exception, HttpStatusCode::HTTP_BAD_REQUEST, 'Api request failed', $transactionId);
        } catch (TransactionException $exception) {
            $this->handleException($exception, $exception->getCode(), 'Failed to handle transaction', $transactionId);
        } catch (\Throwable $exception) {
            $this->handleException($exception, HttpStatusCode::HTTP_BAD_REQUEST, 'Failed to handle webhook', $transactionId);
        }

        $this->releaseLock();

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        $this->ajaxResponse(JsonResponse::success([]));
    }

    /**
     * @throws Throwable
     */
    protected function executeWebhook(string $transactionId): void
    {
        /** @var TransactionService $transactionService */
        $transactionService = $this->module->getService(TransactionService::class);

        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        if (TransactionUtility::isOrderTransaction($transactionId)) {
            $transaction = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
        } else {
            $transaction = $this->module->getApiClient()->payments->get($transactionId);

            if ($transaction->orderId) {
                $transaction = $this->module->getApiClient()->orders->get($transaction->orderId, ['embed' => 'payments']);
            }
        }

        $cartId = $transaction->metadata->cart_id ?? 0;

        if (!$cartId) {
            // TODO webhook structure will change, no need to create custom exception for one time usage
            $logger->error(sprintf('%s - Missing Cart ID', self::FILE_NAME), [
                'transaction_id' => $transactionId,
            ]);

            throw new \Exception(sprintf('Missing Cart ID. Transaction ID: [%s]', $transactionId), HttpStatusCode::HTTP_NOT_FOUND);
        }

        $this->setContext($cartId);

        $transactionService->processTransaction($transaction);
    }

    private function setContext(int $cartId): void
    {
        $cart = new Cart($cartId);

        $this->context->currency = new Currency($cart->id_currency);
        $this->context->customer = new Customer($cart->id_customer);

        $this->context->cart = $cart;
    }

    private function handleException(Throwable $exception, int $httpStatusCode, string $logMessage, string $transactionId = ''): void
    {
        /** @var ErrorHandler $errorHandler */
        $errorHandler = $this->module->getService(ErrorHandler::class);

        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $cartId = 0;
        $orderId = 0;
        $molliePayment = null;
        $psOrderCurrentState = 'no_order';
        $psOrderDateAdd = 'no_order';
        $psOrderReference = 'no_order';

        if ($transactionId) {
            try {
                /** @var PaymentMethodRepository $paymentMethodRepo */
                $paymentMethodRepo = $this->module->getService(PaymentMethodRepository::class);
                $molliePayment = $paymentMethodRepo->getPaymentBy('transaction_id', $transactionId);

                if ($molliePayment) {
                    $cartId = (int) ($molliePayment['cart_id'] ?? 0);
                    $orderId = (int) ($molliePayment['order_id'] ?? 0);
                }

                if (!$orderId && $cartId) {
                    $orderId = (int) Order::getIdByCartId($cartId);
                }

                if ($orderId) {
                    $psOrder = new Order($orderId);
                    $psOrderCurrentState = (int) $psOrder->current_state;
                    $psOrderDateAdd = $psOrder->date_add ?? 'N/A';
                    $psOrderReference = $psOrder->reference ?? 'N/A';
                }
            } catch (\Throwable $e) {
                // silently ignore — we are already in error handling
            }
        }

        $logger->error(sprintf('%s - Failed to handle webhook', self::FILE_NAME), [
            'exceptions' => ExceptionUtility::getExceptions($exception),
            'context' => [
                'transaction_id' => $transactionId,
                'cart_id' => $cartId,
                'order_id' => $orderId,
                'mollie_payment_bank_status' => $molliePayment['bank_status'] ?? 'not_found',
                'mollie_payment_method' => $molliePayment['method'] ?? 'not_found',
                'mollie_payment_created_at' => $molliePayment['created_at'] ?? 'not_found',
                'mollie_payment_updated_at' => $molliePayment['updated_at'] ?? 'not_found',
                'mollie_payment_order_reference' => $molliePayment['order_reference'] ?? 'not_found',
                'ps_order_current_state' => $psOrderCurrentState,
                'ps_order_date_add' => $psOrderDateAdd,
                'ps_order_reference' => $psOrderReference,
                'httpStatusCode' => $httpStatusCode,
                'logMessage' => $logMessage,
                'timestamp' => date('Y-m-d H:i:s'),
            ],
        ]);

        $errorHandler->handle($exception, $httpStatusCode, false);
        $this->releaseLock();
        $this->ajaxResponse(JsonResponse::error(
            $this->module->l('Failed to handle webhook', self::FILE_NAME),
            $httpStatusCode
        ));
    }
}
