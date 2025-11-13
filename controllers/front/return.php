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

use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\Controller\AbstractMollieController;
use Mollie\Factory\CustomerFactory;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\MailService;
use Mollie\Service\PaymentReturnService;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\TransactionUtility;
use Mollie\Validator\OrderCallBackValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../mollie.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieReturnModuleFrontController extends AbstractMollieController
{
    /** @var Mollie */
    public $module;

    private const FILE_NAME = 'return';

    /** @var bool */
    public $ssl = true;

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        try {
            $this->validateRequest();

            if (Tools::getValue('ajax')) {
                $this->processAjax();
            }

            parent::initContent();

            $returnData = $this->handlePaymentReturn();
            $this->assignTemplateVariables($returnData);
        } catch (Exception $e) {
            $logger->error(sprintf('%s - Error: %s', self::FILE_NAME, $e->getMessage()));
            $this->handleError($e);
        }
    }

    private function validateRequest(): void
    {
        $idCart = (int) Tools::getValue('cart_id');
        $key = Tools::getValue('key');

        if (!$idCart) {
            throw new Exception('Cart ID not found');
        }

        /** @var OrderCallBackValidator $orderCallBackValidator */
        $orderCallBackValidator = $this->module->getService(OrderCallBackValidator::class);

        if (!$orderCallBackValidator->validate($key, $idCart)) {
            Tools::redirect('index.php');
        }

        /** @var CustomerFactory $customerFactory */
        $customerFactory = $this->module->getService(CustomerFactory::class);

        $this->context = $customerFactory->recreateFromRequest(
            $this->context->customer->id,
            $key,
            $this->context
        );
    }

    private function handlePaymentReturn(): array
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $idCart = (int) Tools::getValue('cart_id');
        $orderNumber = Tools::getValue('order_number');
        $transactionId = Tools::getValue('transaction_id');
        $customer = $this->context->customer;

        // Validate cart ownership
        $cart = new Cart($idCart);
        if ($cart->id_customer != $customer->id) {
            $logger->error(sprintf(
                '%s - Unauthorized access attempt. Cart ID: %d, Customer ID: %d',
                self::FILE_NAME,
                $idCart,
                $customer->id
            ));
            $this->setWarning($this->module->l('You\'re not authorised to see this page.', self::FILE_NAME));

            Tools::redirect(Context::getContext()->link->getPageLink('index', true));
        }

        /** @var PaymentMethodRepositoryInterface $paymentMethodRepo */
        $paymentMethodRepo = $this->module->getService(PaymentMethodRepositoryInterface::class);

        $paymentInformation = $this->findPaymentInformation($paymentMethodRepo, $idCart, $orderNumber, $transactionId);

        if (empty($paymentInformation)) {
            return [
                'wait' => true,
            ];
        }

        return $this->createReturnData($paymentInformation);
    }

    private function findPaymentInformation(
        PaymentMethodRepositoryInterface $paymentMethodRepo,
        int $idCart,
        string $orderNumber,
        ?string $transactionId
    ): array {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        if ($transactionId) {
            $payment = $paymentMethodRepo->getPaymentBy('transaction_id', $transactionId) ?: [];
            if (empty($payment)) {
                $logger->error(sprintf(
                    '%s - Payment not found by transaction ID: %s',
                    self::FILE_NAME,
                    $transactionId
                ));
            }

            return $payment;
        }

        $paymentInfo = $paymentMethodRepo->getPaymentBy('order_reference', $orderNumber);

        if ($paymentInfo !== false) {
            return $paymentInfo;
        }

        $orderId = (int) Order::getIdByCartId($idCart);
        if ($orderId !== 0) {
            $payment = $paymentMethodRepo->getPaymentBy('order_id', $orderId) ?: [];
            if (empty($payment)) {
                $logger->error(sprintf(
                    '%s - Payment not found by order ID: %d',
                    self::FILE_NAME,
                    $orderId
                ));
            }

            return $payment;
        }

        return [];
    }

    private function createReturnData(array $paymentInformation): array
    {
        $data = [
            'msg_details' => '',
            'wait' => false,
        ];

        if (
            isset($paymentInformation['method'])
            && PaymentMethod::BANKTRANSFER === $paymentInformation['method']
            && PaymentStatus::STATUS_OPEN === $paymentInformation['bank_status']
        ) {
            $data['msg_details'] = $this->module->l(
                'The payment is still being processed. You\'ll be notified when the bank or merchant confirms the payment.',
                self::FILE_NAME
            );
        } else {
            $data['wait'] = true;
        }

        return $data;
    }

    private function assignTemplateVariables(array $returnData): void
    {
        $this->context->smarty->assign([
            'msg_details' => $returnData['msg_details'] ?? null,
            'wait' => $returnData['wait'],
            'link' => $this->context->link,
        ]);

        if ($returnData['wait']) {
            $this->assignWaitTemplateVariables();
            $this->setTemplate('mollie_wait.tpl');
        } else {
            $this->setTemplate('mollie_return.tpl');
        }
    }

    /**
     * Prepend module path if PS version >= 1.7.
     *
     * @param string $template
     * @param array $params
     * @param string|null $locale
     *
     * @throws PrestaShopException
     *
     * @since 3.3.2
     */
    public function setTemplate($template, $params = [], $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $template = "module:mollie/views/templates/front/17_{$template}";
        }

        /* @phpstan-ignore-next-line */
        parent::setTemplate($template, $params, $locale);
    }

    private function assignWaitTemplateVariables(): void
    {
        $this->context->smarty->assign([
            'checkStatusEndpoint' => $this->context->link->getModuleLink(
                $this->module->name,
                'return',
                [
                    'ajax' => 1,
                    'action' => 'getStatus',
                    'transaction_id' => Tools::getValue('transaction_id'),
                    'key' => Tools::getValue('key'),
                    'cart_id' => Tools::getValue('cart_id'),
                    'order_number' => Tools::getValue('order_number'),
                ],
                true
            ),
        ]);
    }

    private function handleError(Exception $e): void
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);
        $logger->error(sprintf(
            '%s - Error occurred: %s. Stack trace: %s',
            self::FILE_NAME,
            $e->getMessage(),
            $e->getTraceAsString()
        ));

        $this->setWarning($this->module->l('An error occurred while processing your payment.', self::FILE_NAME));
        Tools::redirect($this->context->link->getPageLink('index', true));
    }

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function processAjax()
    {
        $logger = $this->module->getService(LoggerInterface::class);

        if (empty($this->context->customer->id)) {
            $logger->debug(sprintf('%s - Customer ID is empty', self::FILE_NAME));

            return;
        }

        switch (Tools::getValue('action')) {
            case 'getStatus':
                $this->processGetStatus();
                break;
        }

        exit;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function processGetStatus()
    {
        header('Content-Type: application/json;charset=UTF-8');

        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $notSuccessfulPaymentMessage = $this->module->l('Your payment was not successful. Try again.', self::FILE_NAME);
        $wrongAmountMessage = $this->module->l('The payment failed because the order and payment amounts are different. Try again.', self::FILE_NAME);

        if (Tools::getValue('failed')) {
            /** @var MailService $mailService */
            $mailService = $this->module->getService(MailService::class);

            try {
                $mailService->sendFailedPaymentMail($this->context->customer);
            } catch (\Throwable $e) {
                /** @var Logger $logger */
                $logger = $this->module->getService(LoggerInterface::class);

                $logger->error(sprintf('%s - Error sending failed payment mail', self::FILE_NAME), [
                        'exceptions' => ExceptionUtility::getExceptions($e),
                ]);
            }

            $this->setWarning($notSuccessfulPaymentMessage);

            Tools::redirect($this->context->link->getPageLink(
                'cart',
                null,
                $this->context->language->id,
                [
                    'action' => 'show',
                    'checkout' => true,
                ]
            ));
        }

        /** @var PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->module->getService(PaymentMethodRepository::class);

        $orderId = (int) Order::getIdByCartId((int) Tools::getValue('cart_id'));

        $dbPayment = $paymentInformation = $orderId != 0 ? $paymentMethodRepo->getPaymentBy('order_id', (int) $orderId)
            : $paymentMethodRepo->getPaymentBy('cart_id', (int) Tools::getValue('cart_id'));

        if (!$dbPayment) {
            exit(json_encode([
                'success' => true,
                'wait' => true,
            ]));
        }

        if (!isset($dbPayment['cart_id']) || !Validate::isLoadedObject($cart = new Cart($dbPayment['cart_id']))) {
            $logger->error(sprintf(
                '%s - Invalid cart ID in payment data: %s',
                self::FILE_NAME,
                $dbPayment['cart_id'] ?? 'not set'
            ));

            exit(json_encode([
                'success' => false,
            ]));
        }

        $transactionId = $paymentInformation['transaction_id'] ?: Tools::getValue('transaction_id');

        /* @phpstan-ignore-next-line */
        $orderId = (int) Order::getIdByCartId((int) $cart->id);
        /** @phpstan-ignore-line */
        $order = new Order((int) $orderId);

        if ((int) $cart->id_customer !== (int) $this->context->customer->id) {
            $logger->error(
                sprintf(
                    '%s - Cart ownership mismatch',
                    self::FILE_NAME
                ),
                [
                    'cart_customer_id' => $cart->id_customer,
                    'context_customer_id' => $this->context->customer->id,
                ]
            );

            exit(json_encode([
                'success' => false,
            ]));
        }

        if (!Tools::isSubmit('module')) {
            $_GET['module'] = $this->module->name;
        }

        $isOrder = TransactionUtility::isOrderTransaction($transactionId);
        if ($isOrder) {
            $transaction = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
        } else {
            $transaction = $this->module->getApiClient()->payments->get($transactionId);
        }

        $orderStatus = $transaction->status;

        if ('order' === $transaction->resource) {
            $payments = ArrayUtility::getLastElement($transaction->_embedded->payments);
            $orderStatus = $payments->status;
        }

        /** @var PaymentReturnService $paymentReturnService */
        $paymentReturnService = $this->module->getService(PaymentReturnService::class);

        switch ($orderStatus) {
            case PaymentStatus::STATUS_OPEN:
            case PaymentStatus::STATUS_PENDING:
                if ($transaction->mode === 'test') {
                    $this->setWarning($this->module->l('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.', self::FILE_NAME));
                    $response = $paymentReturnService->handleTestPendingStatus();
                    break;
                }

                $response = $paymentReturnService->handleStatus(
                    $order,
                    $transaction,
                    $paymentReturnService::PENDING
                );
                break;
            case PaymentStatus::STATUS_PAID:
            case PaymentStatus::STATUS_AUTHORIZED:
                $transactionInfo = $paymentMethodRepo->getPaymentBy('transaction_id', $transaction->id);

                if ($transaction->resource === Config::MOLLIE_API_STATUS_PAYMENT && $transaction->hasRefunds()) {
                    if (isset($transactionInfo['reason']) && $transactionInfo['reason'] === Config::WRONG_AMOUNT_REASON) {
                        $this->setWarning($wrongAmountMessage);
                    } else {
                        $this->setWarning($notSuccessfulPaymentMessage);
                    }

                    $response = $paymentReturnService->handleFailedStatus($transaction);
                    break;
                }

                if (isset($transactionInfo['reason']) && $transactionInfo['reason'] === Config::WRONG_AMOUNT_REASON) {
                    $this->setWarning($wrongAmountMessage);
                    $response = $paymentReturnService->handleFailedStatus($transaction);
                    break;
                }
                $response = $paymentReturnService->handleStatus(
                    $order,
                    $transaction,
                    $paymentReturnService::DONE
                );

                $paymentMethodRepo->setPaymentAsSeen($transaction->id);
                break;
            case PaymentStatus::STATUS_EXPIRED:
            case PaymentStatus::STATUS_CANCELED:
            case PaymentStatus::STATUS_FAILED:
                $transactionInfo = $paymentMethodRepo->getPaymentBy('transaction_id', $transaction->id);
                if (isset($transactionInfo['reason']) && $transactionInfo['reason'] === Config::WRONG_AMOUNT_REASON) {
                    $this->setWarning($wrongAmountMessage);
                } else {
                    $this->setWarning($notSuccessfulPaymentMessage);
                }

                $response = $paymentReturnService->handleFailedStatus($transaction);
                break;
            default:
                exit();
        }

        exit(json_encode($response));
    }

    private function setWarning($message)
    {
        /* @phpstan-ignore-next-line */
        $this->warning[] = $message;

        $this->context->cookie->__set('mollie_payment_canceled_error', json_encode($this->warning));
    }
}
