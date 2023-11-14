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
use Mollie\Controller\AbstractMollieController;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Infrastructure\Response\JsonResponse;
use Mollie\Logger\PrestaLoggerInterface;
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
        /** @var PrestaLoggerInterface $logger */
        $logger = $this->module->getService(PrestaLoggerInterface::class);

        /** @var ErrorHandler $errorHandler */
        $errorHandler = $this->module->getService(ErrorHandler::class);

        /** @var ToolsAdapter $tools */
        $tools = $this->module->getService(ToolsAdapter::class);

        $logger->info(sprintf('%s - Controller called', self::FILE_NAME));

        if (!$this->module->getApiClient()) {
            $logger->error(sprintf('Unauthorized in %s', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Unauthorized', self::FILE_NAME),
                HttpStatusCode::HTTP_UNAUTHORIZED
            ));
        }

        $transactionId = (string) $tools->getValue('id');

        if (!$transactionId) {
            $logger->error(sprintf('Missing transaction id %s', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Missing transaction id', self::FILE_NAME),
                HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY
            ));
        }

        $lockResult = $this->applyLock(sprintf(
            '%s-%s',
            self::FILE_NAME,
            $transactionId
        ));

        if (!$lockResult->isSuccessful()) {
            $logger->error(sprintf('Resource conflict in %s', self::FILE_NAME));

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Resource conflict', self::FILE_NAME),
                HttpStatusCode::HTTP_CONFLICT
            ));
        }

        try {
            $this->executeWebhook($transactionId);
        } catch (\Throwable $exception) {
            $logger->error('Failed to handle webhook', [
                'Exception message' => $exception->getMessage(),
                'Exception code' => $exception->getCode(),
            ]);

            $errorHandler->handle($exception, $exception->getCode(), false);

            $this->releaseLock();

            $this->ajaxResponse(JsonResponse::error(
                $this->module->l('Failed to handle webhook', self::FILE_NAME),
                $exception->getCode()
            ));
        }

        $this->releaseLock();

        $logger->info(sprintf('%s - Controller action ended', self::FILE_NAME));

        $this->ajaxResponse(JsonResponse::success([]));
    }

    /**
     * @throws Throwable
     */
    protected function executeWebhook(string $transactionId): void
    {
        /** @var TransactionService $transactionService */
        $transactionService = $this->module->getService(TransactionService::class);

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

            throw new \Exception(
                sprintf('Missing Cart ID. Transaction ID: [%s]', $transactionId),
                HttpStatusCode::HTTP_NOT_FOUND
            );
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
}
