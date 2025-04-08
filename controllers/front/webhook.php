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
use Mollie\Service\TransactionService;
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

        $logger->debug(sprintf('%s - getApiClient success', self::FILE_NAME));

        if (!$tools->getValue('security_token')) {
            $logger->debug(sprintf('%s - Missing security token', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Missing security token', self::FILE_NAME),
                HttpStatusCode::HTTP_BAD_REQUEST
            ));
        }

        $transactionId = (string) $tools->getValue('id');

        $logger->debug(sprintf('%s - get ID successful: %s', self::FILE_NAME, $transactionId));

        if (!$transactionId) {
            $logger->error(sprintf('%s - Missing transaction ID', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Missing transaction id', self::FILE_NAME),
                HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY
            ));
        }

        $logger->debug(sprintf('%s - Transaction ID exists: %s', self::FILE_NAME, $transactionId));

        $lockResult = $this->applyLock(sprintf(
            '%s-%s',
            self::FILE_NAME,
            $tools->getValue('security_token')
        ));

        $logger->debug(sprintf('%s - lock result: %s', self::FILE_NAME, $lockResult));

        if (!$lockResult->isSuccessful()) {
            $logger->error(sprintf('%s - Resource conflict', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Resource conflict', self::FILE_NAME),
                HttpStatusCode::HTTP_CONFLICT
            ));
        }

        $logger->debug(sprintf('%s - Start executeWebhook', self::FILE_NAME));

        try {
            $this->executeWebhook($transactionId);
        } catch (ApiException $exception) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $exception->getMessage()));
            $this->handleException($exception, HttpStatusCode::HTTP_BAD_REQUEST, 'Api request failed');
        } catch (TransactionException $exception) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $exception->getMessage()));
            $this->handleException($exception, $exception->getCode(), 'Failed to handle transaction');
        } catch (\Throwable $exception) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $exception->getMessage()));
            $this->handleException($exception, HttpStatusCode::HTTP_BAD_REQUEST, 'Failed to handle webhook');
        }

        $logger->debug(sprintf('%s - Webhook executed successfully', self::FILE_NAME));

        $this->releaseLock();

        $logger->debug(sprintf('%s - Lock released', self::FILE_NAME));

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));

        $this->ajaxResponse(JsonResponse::success([]));
    }

    /**
     * @throws Throwable
     */
    protected function executeWebhook(string $transactionId): void
    {

        try {
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

            $logger->debug(sprintf('%s - executeWebhook 1', self::FILE_NAME), [
                'transaction_id' => $transactionId,
                'cart_id' => $cartId,
            ]);

            if (!$cartId) {
                // TODO webhook structure will change, no need to create custom exception for one time usage
                $logger->error(sprintf('%s - Missing Cart ID', self::FILE_NAME), [
                    'transaction_id' => $transactionId,
                ]);

                throw new \Exception(sprintf('Missing Cart ID. Transaction ID: [%s]', $transactionId), HttpStatusCode::HTTP_NOT_FOUND);
            }

            $logger->debug(sprintf('%s - executeWebhook 2', self::FILE_NAME));

            $this->setContext($cartId);

            $logger->debug(sprintf('%s - Does cart exists: %s', self::FILE_NAME, $this->context->cart->orderExists()), [
                'cart_id' => $cartId,
            ]);

            $logger->debug(sprintf('%s - executeWebhook 3', self::FILE_NAME));

            $transactionService->processTransaction($transaction);

            $logger->debug(sprintf('%s - executeWebhook 4', self::FILE_NAME));
        } catch (ApiException $e) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $e->getMessage()));
        } catch (TransactionException $e) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $e->getMessage()));
        } catch (PrestaShopDatabaseException $e) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $e->getMessage()));
        } catch (PrestaShopException $e) {
            $logger->debug(sprintf('%s - Error: %s', self::FILE_NAME, $e->getMessage()));
        }
    }

    private function setContext(int $cartId): void
    {
        $cart = new Cart($cartId);

        $this->context->currency = new Currency($cart->id_currency);
        $this->context->customer = new Customer($cart->id_customer);

        $this->context->cart = $cart;
    }

    private function handleException(Throwable $exception, int $httpStatusCode, string $logMessage): void
    {
        /** @var ErrorHandler $errorHandler */
        $errorHandler = $this->module->getService(ErrorHandler::class);

        $errorHandler->handle($exception, $httpStatusCode, false);
        $this->releaseLock();
        $this->ajaxResponse(JsonResponse::error(
            $this->module->l('Failed to handle webhook', self::FILE_NAME),
            $httpStatusCode
        ));
    }
}
