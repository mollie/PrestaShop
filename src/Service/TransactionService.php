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

namespace Mollie\Service;

use Cart;
use Configuration;
use Currency;
use Db;
use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;
use Mollie\Handler\Order\OrderCreationHandler;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Utility\MollieStatusUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\OrderNumberUtility;
use Mollie\Utility\TextGeneratorUtility;
use Mollie\Utility\TransactionUtility;
use MolPaymentMethod;
use Order;
use OrderPayment;
use PrestaShopDatabaseException;
use PrestaShopException;
use PrestaShopLogger;

class TransactionService
{
    /**
     * @var Mollie
     */
    private $module;

    /**
     * @var OrderStatusService
     */
    private $orderStatusService;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;
    /**
     * @var OrderCreationHandler
     */
    private $orderCreationHandler;
    /**
     * @var PaymentMethodService
     */
    private $paymentMethodService;
    /** @var MollieOrderCreationService */
    private $mollieOrderCreationService;

    public function __construct(
        Mollie $module,
        OrderStatusService $orderStatusService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderCreationHandler $orderCreationHandler,
        PaymentMethodService $paymentMethodService,
        MollieOrderCreationService $mollieOrderCreationService
    ) {
        $this->module = $module;
        $this->orderStatusService = $orderStatusService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderCreationHandler = $orderCreationHandler;
        $this->paymentMethodService = $paymentMethodService;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
    }

    /**
     * @param MolliePaymentAlias|MollieOrderAlias $apiPayment
     *
     * @return string|MolliePaymentAlias Returns a single payment (in case of Orders API it returns the highest prio Payment object) or status string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ApiException
     *
     * @since 3.3.0
     * @since 3.3.2 Returns the ApiPayment / ApiOrder instead of OK string, NOT OK/NO ID stays the same
     * @since 3.3.2 Returns the ApiPayment instead of ApiPayment / ApiOrder
     */
    public function processTransaction($apiPayment)
    {
        if (empty($apiPayment)) {
            if (Configuration::get(Config::MOLLIE_DEBUG_LOG) >= Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request without proper transaction ID.', Config::WARNING);
            }

            return $this->module->l('Transaction failed', 'webhook');
        }

        $transactionNotUsedMessage = $this->module->l('Transaction is no longer used', 'webhook');
        $orderIsCreateMessage = $this->module->l('Order is already created', 'webhook');

        /** @var int $orderId */
        $orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);

        $cart = new Cart($apiPayment->metadata->cart_id);

        $key = Mollie\Utility\SecureKeyUtility::generateReturnKey(
            $cart->secure_key,
            $cart->id_customer,
            $cart->id,
            $this->module->name
        );

        switch ($apiPayment->resource) {
            case Config::MOLLIE_API_STATUS_PAYMENT:
                if ($key !== $apiPayment->metadata->secure_key) {
                    break;
                }
                if (!$apiPayment->metadata->cart_id) {
                    break;
                }
                if ($apiPayment->hasRefunds() || $apiPayment->hasChargebacks()) {
                    if (strpos($apiPayment->description, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                        return $transactionNotUsedMessage;
                    }
                    if (isset($apiPayment->amount->value, $apiPayment->amountRefunded->value)
                        && NumberUtility::isLowerOrEqualThan($apiPayment->amount->value, $apiPayment->amountRefunded->value)
                    ) {
                        $this->orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                    } else {
                        $this->orderStatusService->setOrderStatus($orderId, Config::PARTIAL_REFUND_CODE);
                    }
                } else {
                    if (!$orderId && MollieStatusUtility::isPaymentFinished($apiPayment->status)) {
                        $orderId = $this->orderCreationHandler->createOrder($apiPayment, $cart->id);
                        if (!$orderId) {
                            return $orderIsCreateMessage;
                        }
                        $payment = $this->module->api->payments->get($apiPayment->id);
                        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
                        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($apiPayment->method, $environment);
                        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);
                        $payment->description = TextGeneratorUtility::generateDescriptionFromCart($paymentMethodObj->description, $orderId);
                        $payment->update();
                    } elseif (strpos($apiPayment->description, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                        return $transactionNotUsedMessage;
                    } else {
                        $this->orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    }
                    $orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
                }
                break;
            case Config::MOLLIE_API_STATUS_ORDER:
                if (!$apiPayment->metadata->cart_id) {
                    break;
                }
                if ($key !== $apiPayment->metadata->secure_key) {
                    break;
                }

                $isKlarnaOrder = in_array($apiPayment->method, Config::KLARNA_PAYMENTS, false);

                if (!$orderId && MollieStatusUtility::isPaymentFinished($apiPayment->status)) {
                    $orderId = $this->orderCreationHandler->createOrder($apiPayment, $cart->id, $isKlarnaOrder);
                    if (!$orderId) {
                        return $orderIsCreateMessage;
                    }
                    $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
                    $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($apiPayment->method, $environment);
                    $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);
                    $orderNumber = TextGeneratorUtility::generateDescriptionFromCart($paymentMethodObj->description, $orderId);
                    $apiPayment->orderNumber = $orderNumber;
                    $payments = $apiPayment->payments();

                    /** @var Payment $payment */
                    foreach ($payments as $payment) {
                        $payment->description = 'Order ' . $orderNumber;
                        $payment->update();
                    }
                    $apiPayment->update();
                } elseif ($apiPayment->amountRefunded) {
                    if (strpos($apiPayment->orderNumber, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                        return $transactionNotUsedMessage;
                    }
                    if (isset($apiPayment->amount->value, $apiPayment->amountRefunded->value)
                        && NumberUtility::isLowerOrEqualThan($apiPayment->amount->value, $apiPayment->amountRefunded->value)
                    ) {
                        $this->orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                    } else {
                        if ($apiPayment->method === Config::MOLLIE_VOUCHER_METHOD_ID) {
                            $payment = $apiPayment->payments()[0];
                            if (NumberUtility::isLowerOrEqualThan($payment->details->remainderAmount->value, $apiPayment->amountRefunded->value)) {
                                $this->orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                            }
                        } else {
                            $this->orderStatusService->setOrderStatus($orderId, Config::PARTIAL_REFUND_CODE);
                        }
                    }
                } elseif (strpos($apiPayment->orderNumber, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                    return $transactionNotUsedMessage;
                } else {
                    $isKlarnaDefault = Configuration::get(Config::MOLLIE_KLARNA_INVOICE_ON) === Config::MOLLIE_STATUS_DEFAULT;
                    if (in_array($apiPayment->method, Config::KLARNA_PAYMENTS) && !$isKlarnaDefault && $apiPayment->status === OrderStatus::STATUS_COMPLETED) {
                        $this->orderStatusService->setOrderStatus($orderId, Config::MOLLIE_STATUS_KLARNA_SHIPPED);
                    } else {
                        $this->orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    }
                }

                $orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
        }

        $paymentMethod = $this->paymentMethodRepository->getPaymentBy('transaction_id', $apiPayment->id);
        $order = new Order($orderId);
        if (!$paymentMethod) {
            $this->mollieOrderCreationService->createMolliePayment($apiPayment, $cart->id, $order->reference);
        } else {
            $this->mollieOrderCreationService->updateMolliePaymentReference($apiPayment->id, $order->reference);
        }

        $this->updateTransaction($orderId, $apiPayment);
        // Store status in database
        if (!$this->savePaymentStatus($apiPayment->id, $apiPayment->status, $orderId)) {
            if (Configuration::get(Config::MOLLIE_DEBUG_LOG) >= Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: Could not save Mollie payment status for transaction "' . $apiPayment->id . '". Reason: ' . Db::getInstance()->getMsgError(), Config::WARNING);
            }
        }

        // Log successful webhook requests in extended log mode only
        if (Config::DEBUG_LOG_ALL == Configuration::get(Config::MOLLIE_DEBUG_LOG)) {
            PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request for order ' . (int) $orderId . ' / transaction ' . $apiPayment->id, Config::NOTICE);
        }

        return $apiPayment;
    }

    public function updateOrderTransaction($transactionId, $orderReference)
    {
        $transactionInfos = [];
        $isOrder = TransactionUtility::isOrderTransaction($transactionId);
        if ($isOrder) {
            $transaction = $this->module->api->orders->get($transactionId, ['embed' => 'payments']);
            /** @var PaymentCollection|null $payments */
            $payments = $transaction->payments();

            foreach ($payments as $payment) {
                if (Config::MOLLIE_VOUCHER_METHOD_ID === $transaction->method) {
                    $transactionInfos = $this->getVoucherTransactionInfo($payment, $transactionInfos);
                    $transactionInfos = $this->getVoucherRemainderTransactionInfo($payment, $transactionInfos);
                } else {
                    $transactionInfos = $this->getPaymentTransactionInfo($payment, $transactionInfos);
                }
            }
        } else {
            $transaction = $this->module->api->payments->get($transactionId);
            $transactionInfos = $this->getPaymentTransactionInfo($transaction, $transactionInfos);
        }

        $this->updateOrderPayments($transactionInfos, $orderReference);
    }

    /**
     * @param int $orderId
     * @param MolliePaymentAlias|MollieOrderAlias $transaction
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function updateTransaction($orderId, $transaction)
    {
        $paymentMethod = $this->paymentMethodService->getPaymentMethod($transaction);
        $order = new Order($orderId);
        if (!$order->getOrderPayments()) {
            $this->updateOrderTransaction($transaction->id, $order->reference);
        } else {
            /** @var OrderPayment $orderPayment */
            foreach ($order->getOrderPayments() as $orderPayment) {
                if ($orderPayment->transaction_id) {
                    continue;
                }
                $orderPayment->transaction_id = $transaction->id;
                $orderPayment->payment_method = $paymentMethod->method_name;
                $orderPayment->update();
            }
        }
    }

    /**
     * @param string $transactionId
     * @param string $status
     * @param int $orderId
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function savePaymentStatus($transactionId, $status, $orderId)
    {
        try {
            return Db::getInstance()->update(
                'mollie_payments',
                [
                    'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'bank_status' => pSQL($status),
                    'order_id' => (int) $orderId,
                ],
                '`transaction_id` = \'' . pSQL($transactionId) . '\''
            );
        } catch (PrestaShopDatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @return array
     */
    private function getVoucherTransactionInfo(MolliePaymentAlias $payment, array $transactionInfos)
    {
        foreach ($payment->details->vouchers as $voucher) {
            $transactionInfos[] = [
                'paymentName' => $voucher->issuer,
                'amount' => $voucher->amount->value,
                'currency' => $voucher->amount->currency,
                'transactionId' => $payment->id,
            ];
        }

        return $transactionInfos;
    }

    /**
     * @return array
     */
    private function getVoucherRemainderTransactionInfo(MolliePaymentAlias $payment, array $transactionInfos)
    {
        if ($payment->details->remainderMethod) {
            $transactionInfos[] = [
                'paymentName' => $payment->details->remainderMethod,
                'amount' => $payment->details->remainderAmount->value,
                'currency' => $payment->details->remainderAmount->currency,
                'transactionId' => $payment->id,
            ];
        }

        return $transactionInfos;
    }

    /**
     * @return array
     */
    private function getPaymentTransactionInfo(MolliePaymentAlias $payment, array $transactionInfos)
    {
        $transactionInfos[] = [
            'paymentName' => $payment->method,
            'amount' => $payment->amount->value,
            'currency' => $payment->amount->currency,
            'transactionId' => $payment->id,
        ];

        return $transactionInfos;
    }

    /**
     * @param string $orderReference
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function updateOrderPayments(array $transactionInfos, $orderReference)
    {
        foreach ($transactionInfos as $transactionInfo) {
            $orderPayment = new OrderPayment();
            $orderPayment->order_reference = $orderReference;
            $orderPayment->amount = $transactionInfo['amount'];
            $orderPayment->payment_method = $transactionInfo['paymentName'];
            $orderPayment->transaction_id = $transactionInfo['transactionId'];
            $orderPayment->id_currency = Currency::getIdByIsoCode($transactionInfo['currency']);

            $orderPayment->add();
        }
    }
}
