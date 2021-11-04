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
use Mollie\Utility\TransactionUtility;
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

    public function __construct(
        Mollie $module,
        OrderStatusService $orderStatusService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderCreationHandler $orderCreationHandler
    ) {
        $this->module = $module;
        $this->orderStatusService = $orderStatusService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderCreationHandler = $orderCreationHandler;
    }

    /**
     * @param MolliePaymentAlias|MollieOrderAlias $transaction
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
    public function processTransaction($transaction)
    {
        if (empty($transaction)) {
            if (Configuration::get(Config::MOLLIE_DEBUG_LOG) >= Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request without proper transaction ID.', Config::WARNING);
            }

            return $this->module->l('Transaction failed', 'webhook');
        }

        // Ensure that we are dealing with a Payment object, in case of transaction ID or Payment object w/ Order ID, convert
        if ($transaction instanceof MolliePaymentAlias) {
            if (!empty($transaction->orderId) && TransactionUtility::isOrderTransaction($transaction->orderId)) {
                // Part of order
                $transaction = $this->module->api->orders->get($transaction->orderId, ['embed' => 'payments']);
            } else {
                // Single payment
                $apiPayment = $transaction;
            }
        }

        if (!empty($transaction->id) && TransactionUtility::isOrderTransaction(($transaction->id))) {
            $apiPayment = $this->module->api->orders->get($transaction->id, ['embed' => 'payments']);
        }

        if (!isset($apiPayment)) {
            return $this->module->l('Transaction failed', 'webhook');
        }
        $transactionNotUsedMessage = $this->module->l('Transaction is no longer used', 'webhook');

        /** @var int $orderId */
        $orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);

        $cart = new Cart($apiPayment->metadata->cart_id);

        $key = Mollie\Utility\SecureKeyUtility::generateReturnKey(
            $cart->secure_key,
            $cart->id_customer,
            $cart->id,
            $this->module->name
        );

        switch ($transaction->resource) {
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
                    if (isset($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
                        && NumberUtility::isLowerOrEqualThan($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
                    ) {
                        $this->orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                    } else {
                        $this->orderStatusService->setOrderStatus($orderId, Config::PARTIAL_REFUND_CODE);
                    }
                } else {
                    if (!$orderId && MollieStatusUtility::isPaymentFinished($apiPayment->status)) {
                        $orderId = $this->orderCreationHandler->createOrder($apiPayment, $cart->id);
                        $order = new Order($orderId);
                        $payment = $this->module->api->payments->get($apiPayment->id);
                        $payment->description = $order->reference;
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
                    $order = new Order($orderId);
                    $apiPayment->orderNumber = $order->reference;
                    $payments = $apiPayment->payments();

                    /** @var Payment $payment */
                    foreach ($payments as $payment) {
                        $payment->description = 'Order ' . $order->reference;
                        $payment->update();
                    }
                    $apiPayment->update();
                } elseif (strpos($apiPayment->orderNumber, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                    return $transactionNotUsedMessage;
                } else {
                    if (in_array($apiPayment->method, Config::KLARNA_PAYMENTS) && $apiPayment->status === OrderStatus::STATUS_COMPLETED) {
                        $this->orderStatusService->setOrderStatus($orderId, Config::MOLLIE_STATUS_KLARNA_SHIPPED);
                    } else {
                        $this->orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    }
                }

                $orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
        }

        // Store status in database
        if (!$this->savePaymentStatus($transaction->id, $apiPayment->status, $orderId)) {
            if (Configuration::get(Config::MOLLIE_DEBUG_LOG) >= Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: Could not save Mollie payment status for transaction "' . $transaction->id . '". Reason: ' . Db::getInstance()->getMsgError(), Config::WARNING);
            }
        }

        // Log successful webhook requests in extended log mode only
        if (Config::DEBUG_LOG_ALL == Configuration::get(Config::MOLLIE_DEBUG_LOG)) {
            PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request for order ' . (int) $orderId . ' / transaction ' . $transaction->id, Config::NOTICE);
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
            $this->paymentMethodRepository->tryAddOrderReferenceColumn();
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
