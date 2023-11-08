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
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Infrastructure\Adapter\Lock;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Service\TransactionService;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieWebhookModuleFrontController extends AbstractMollieController
{
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
    protected function displayMaintenancePage(): void
    {
    }

    public function initContent(): void
    {
        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        /** @var ErrorHandler $errorHandler */
        $errorHandler = $this->module->getService(ErrorHandler::class);

        if ((int) Configuration::get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_ALL) {
            $logger->info('Mollie incoming webhook: ' . Tools::file_get_contents('php://input'));
        }

        $transactionId = (string) Tools::getValue('id');

        if (!$transactionId) {
            $this->respond('failed', HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY, 'Missing transaction id');

            exit;
        }

        if (!$this->module->getApiClient()) {
            $this->respond('failed', HttpStatusCode::HTTP_UNAUTHORIZED, 'API key is missing or incorrect');

            exit;
        }

        /** @var Lock $lock */
        $lock = $this->module->getService(Lock::class);

        try {
            $lock->create($transactionId);

            $acquired = $lock->acquire();
        } catch (\Throwable $exception) {
            $logger->error(
                'Failed to lock process',
                [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                    'transaction_id' => $transactionId,
                ]
            );

            $errorHandler->handle($exception, $exception->getCode(), false);

            $this->respond('failed', HttpStatusCode::HTTP_BAD_REQUEST, 'Failed to lock process');

            exit;
        }

        if (!$acquired) {
            $this->respond('failed', HttpStatusCode::HTTP_BAD_REQUEST, 'Another process is locked');

            exit;
        }

        try {
            $result = $this->executeWebhook($transactionId);
        } catch (\Throwable $exception) {
            $logger->error(
                'Failed to process webhook',
                [
                    'Exception message' => $exception->getMessage(),
                    'Exception code' => $exception->getCode(),
                    'transaction_id' => $transactionId,
                ]
            );

            $errorHandler->handle($exception, $exception->getCode(), false);

            $this->respond('failed', HttpStatusCode::HTTP_BAD_REQUEST, 'Failed to process webhook');

            exit;
        }

        $this->respond('success', HttpStatusCode::HTTP_OK, $result);
    }

    /**
     * @throws \Throwable
     */
    protected function executeWebhook(string $transactionId): string
    {
        /** @var TransactionService $transactionService */
        $transactionService = $this->module->getService(TransactionService::class);

        // TODO even if transaction is not found, we should return OK 200

        if (TransactionUtility::isOrderTransaction($transactionId)) {
            $transaction = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
        } else {
            $transaction = $this->module->getApiClient()->payments->get($transactionId);

            if ($transaction->orderId) {
                $transaction = $this->module->getApiClient()->orders->get($transaction->orderId, ['embed' => 'payments']);
            }
        }

        $metaData = $transaction->metadata;
        $cartId = $metaData->cart_id ?? 0;
        $this->setContext($cartId);
        $payment = $transactionService->processTransaction($transaction);

        if (is_string($payment)) {
            return $payment;
        }

        return 'OK';
    }

    private function setContext(int $cartId): void
    {
        if (!$cartId) {
            return;
        }
        $cart = new Cart($cartId);
        $this->context->currency = new Currency($cart->id_currency);
        $this->context->customer = new Customer($cart->id_customer);
        $this->context->cart = $cart;
    }
}
