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
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\PaymentReturnService;
use Mollie\Utility\ArrayUtility;
use Mollie\Utility\TransactionUtility;
use Mollie\Validator\OrderCallBackValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../mollie.php';

class MollieReturnModuleFrontController extends AbstractMollieController
{
    /** @var Mollie */
    public $module;

    const FILE_NAME = 'return';

    /** @var bool */
    public $ssl = true;

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        $idCart = (int) Tools::getValue('cart_id');
        $key = Tools::getValue('key');
        $orderNumber = Tools::getValue('order_number');
        $transactionId = Tools::getValue('transaction_id');
        $context = Context::getContext();
        $customer = $context->customer;

        /** @var OrderCallBackValidator $orderCallBackValidator */
        $orderCallBackValidator = $this->module->getService(OrderCallBackValidator::class);

        if (!$orderCallBackValidator->validate($key, $idCart)) {
            Tools::redirectLink('index.php');
        }

        /** @var CustomerFactory $customerFactory */
        $customerFactory = $this->module->getService(CustomerFactory::class);
        $this->context = $customerFactory->recreateFromRequest($customer->id, $key, $this->context);
        if (Tools::getValue('ajax')) {
            $this->processAjax();
            exit;
        }

        parent::initContent();

        $data = [];
        $cart = null;

        /** @var PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->module->getService(PaymentMethodRepository::class);
        if (Tools::getIsset('cart_id')) {
            $idCart = (int) Tools::getValue('cart_id');

            // Check if user that's seeing this is the cart-owner
            $cart = new Cart($idCart);
            $data['auth'] = (int) $cart->id_customer === $customer->id;
            if ($data['auth']) {
                if ($transactionId) {
                    $data['mollie_info'] = $paymentMethodRepo->getPaymentBy('transaction_id', (string) $transactionId);
                } else {
                    $data['mollie_info'] = $paymentMethodRepo->getPaymentBy('order_reference', (string) $orderNumber);
                }
            }
        }

        if (isset($data['auth']) && $data['auth']) {
            // any paid payments for this cart?

            if (false === $data['mollie_info']) {
                $data['mollie_info'] = $paymentMethodRepo->getPaymentBy('order_id', (int) Order::getOrderByCartId($idCart));
            }
            if (false === $data['mollie_info']) {
                $data['mollie_info'] = [];
                $data['msg_details'] = $this->module->l('There is no order with this ID.', self::FILE_NAME);
            } elseif (PaymentMethod::BANKTRANSFER === $data['mollie_info']['method']
                && PaymentStatus::STATUS_OPEN === $data['mollie_info']['bank_status']
            ) {
                $data['msg_details'] = $this->module->l('The payment is still being processed. You\'ll be notified when the bank or merchant confirms the payment./merchant.', self::FILE_NAME);
            } else {
                $data['wait'] = true;
            }
        } else {
            // Not allowed? Don't make query but redirect.
            $data['mollie_info'] = [];
            $data['msg_details'] = $this->module->l('You\'re not authorised to see this page.', self::FILE_NAME);
            Tools::redirect(Context::getContext()->link->getPageLink('index', true));
        }

        $this->context->smarty->assign($data);
        $this->context->smarty->assign('link', $this->context->link);

        if (!empty($data['wait'])) {
            $this->context->smarty->assign(
                'checkStatusEndpoint',
                $this->context->link->getModuleLink(
                    $this->module->name,
                    'return',
                    [
                        'ajax' => 1,
                        'action' => 'getStatus',
                        'transaction_id' => $data['mollie_info']['transaction_id'],
                        'key' => $key,
                        'cart_id' => $idCart,
                        'order_number' => $orderNumber,
                    ],
                    true
                )
            );
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

    /**
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function processAjax()
    {
        if (empty($this->context->customer->id)) {
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
        /** @var PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->module->getService(PaymentMethodRepository::class);

        $transactionId = Tools::getValue('transaction_id');
        $dbPayment = $paymentMethodRepo->getPaymentBy('transaction_id', $transactionId);
        if (!$dbPayment) {
            exit(json_encode([
                'success' => false,
            ]));
        }
        if (!isset($dbPayment['cart_id']) || !Validate::isLoadedObject($cart = new Cart($dbPayment['cart_id']))) {
            exit(json_encode([
                'success' => false,
            ]));
        }

        /* @phpstan-ignore-next-line */
        $orderId = (int) Order::getOrderByCartId((int) $cart->id);
        /** @phpstan-ignore-line */
        $order = new Order((int) $orderId);

        if ((int) $cart->id_customer !== (int) $this->context->customer->id) {
            exit(json_encode([
                'success' => false,
            ]));
        }

        if (!Tools::isSubmit('module')) {
            $_GET['module'] = $this->module->name;
        }

        $isOrder = TransactionUtility::isOrderTransaction($transactionId);
        if ($isOrder) {
            $transaction = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
        } else {
            $transaction = $this->module->api->payments->get($transactionId);
        }

        $orderStatus = $transaction->status;

        if ('order' === $transaction->resource) {
            $payments = ArrayUtility::getLastElement($transaction->_embedded->payments);
            $orderStatus = $payments->status;
        }

        $notSuccessfulPaymentMessage = $this->module->l('Your payment was not successful. Try again.', self::FILE_NAME);
        $wrongAmountMessage = $this->module->l('The payment failed because the order and payment amounts are different. Try again.', self::FILE_NAME);

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
