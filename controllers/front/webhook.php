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

use Mollie\Api\Exceptions\ApiException;
use Mollie\Config\Config;
use Mollie\Controller\AbstractMollieController;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\TransactionException;
use Mollie\Handler\ErrorHandler\ErrorHandler;
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
    protected function displayMaintenancePage()
    {
    }

    /**
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if ((int) Configuration::get(Config::MOLLIE_DEBUG_LOG) === Config::DEBUG_LOG_ALL) {
            PrestaShopLogger::addLog('Mollie incoming webhook: ' . Tools::file_get_contents('php://input'));
        }

        try {
            exit($this->executeWebhook());
        } catch (\Throwable $exception) {
            PrestaShopLogger::addLog('Error occurred: ' . $exception->getMessage(), 3, null, 'Mollie');
        }
    }

    /**
     * @return string
     *
     * @throws ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function executeWebhook()
    {
        /** @var TransactionService $transactionService */
        $transactionService = $this->module->getService(TransactionService::class);

        $transactionId = Tools::getValue('id');
        if (!$transactionId) {
            $this->respond('failed', HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY, 'Missing transaction id');
        }

        if (!$this->module->getApiClient()) {
            $this->respond('failed', HttpStatusCode::HTTP_UNAUTHORIZED, 'API key is missing or incorrect');
        }

        try {
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
        } catch (TransactionException $e) {
            /** @var ErrorHandler $errorHandler */
            $errorHandler = $this->module->getService(ErrorHandler::class);
            $errorHandler->handle($e, $e->getCode(), false);
            $this->respond('failed', $e->getCode(), $e->getMessage());
        }

        /* @phpstan-ignore-next-line */
        if (is_string($payment)) {
            return $payment;
        }

        return 'OK';
    }

    private function setContext(int $cartId)
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
