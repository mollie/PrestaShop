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
use Currency;
use Db;
use Mollie;
use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\ShipmentCannotBeSentException;
use Mollie\Exception\TransactionException;
use Mollie\Factory\ModuleFactory;
use Mollie\Handler\Order\OrderCreationHandler;
use Mollie\Handler\Order\OrderPaymentFeeHandler;
use Mollie\Handler\Shipment\ShipmentSenderHandlerInterface;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\MollieStatusUtility;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\OrderNumberUtility;
use Mollie\Utility\SecureKeyUtility;
use Mollie\Utility\TextGeneratorUtility;
use Mollie\Utility\TransactionUtility;
use MolPaymentMethod;
use Order;
use OrderPayment;
use PrestaShopDatabaseException;
use PrestaShopException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TransactionService
{
    const FILE_NAME = 'TransactionService';

    /** @var Mollie */
    private $module;

    /** @var OrderStatusService */
    private $orderStatusService;

    /** @var PaymentMethodRepositoryInterface */
    private $paymentMethodRepository;

    /** @var OrderCreationHandler */
    private $orderCreationHandler;

    /** @var PaymentMethodService */
    private $paymentMethodService;

    /** @var MollieOrderCreationService */
    private $mollieOrderCreationService;

    /** @var OrderPaymentFeeHandler */
    private $orderPaymentFeeHandler;

    /** @var ShipmentSenderHandlerInterface */
    private $shipmentSenderHandler;

    /** @var PrestaLoggerInterface */
    private $logger;

    /** @var ExceptionService */
    private $exceptionService;

    /** @var ConfigurationAdapter */
    private $configurationAdapter;

    public function __construct(
        ModuleFactory $module,
        OrderStatusService $orderStatusService,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderCreationHandler $orderCreationHandler,
        PaymentMethodService $paymentMethodService,
        MollieOrderCreationService $mollieOrderCreationService,
        OrderPaymentFeeHandler $orderPaymentFeeHandler,
        ShipmentSenderHandlerInterface $shipmentSenderHandler,
        PrestaLoggerInterface $logger,
        ExceptionService $exceptionService,
        ConfigurationAdapter $configurationAdapter
    ) {
        $this->module = $module->getModule();
        $this->orderStatusService = $orderStatusService;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderCreationHandler = $orderCreationHandler;
        $this->paymentMethodService = $paymentMethodService;
        $this->mollieOrderCreationService = $mollieOrderCreationService;
        $this->orderPaymentFeeHandler = $orderPaymentFeeHandler;
        $this->shipmentSenderHandler = $shipmentSenderHandler;
        $this->logger = $logger;
        $this->exceptionService = $exceptionService;
        $this->configurationAdapter = $configurationAdapter;
    }

    /**
     * @param Payment|MollieOrderAlias $apiPayment
     *
     * @return string|Payment Returns a single payment (in case of Orders API it returns the highest prio Payment object) or status string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ApiException
     * @throws TransactionException
     *
     * @since 3.3.0
     * @since 3.3.2 Returns the ApiPayment / ApiOrder instead of OK string, NOT OK/NO ID stays the same
     * @since 3.3.2 Returns the ApiPayment instead of ApiPayment / ApiOrder
     */
    public function processTransaction($apiPayment)
    {
        if (empty($apiPayment)) {
            if ($this->configurationAdapter->get(Config::MOLLIE_DEBUG_LOG) >= Config::DEBUG_LOG_ERRORS) {
                $this->logger->error(sprintf('%s - Received webhook request without proper transaction ID', self::FILE_NAME));
            }

            throw new TransactionException('Transaction failed', HttpStatusCode::HTTP_BAD_REQUEST);
        }

        $orderDescription = $apiPayment->description ?? $apiPayment->orderNumber;

        $paymentMethod = $this->paymentMethodRepository->getPaymentBy('transaction_id', $apiPayment->id);

        if (!$paymentMethod) {
            $this->mollieOrderCreationService->createMolliePayment($apiPayment, (int) $apiPayment->metadata->cart_id, $orderDescription);
        }

        /** @var int $orderId */
        $orderId = Order::getIdByCartId((int) $apiPayment->metadata->cart_id);

        $cart = new Cart($apiPayment->metadata->cart_id);

        $key = SecureKeyUtility::generateReturnKey(
            $cart->id_customer,
            $cart->id,
            $this->module->name
        );

        // remove after few releases
        $deprecatedKey = SecureKeyUtility::deprecatedGenerateReturnKey(
            $cart->secure_key,
            $cart->id_customer,
            $cart->id,
            $this->module->name
        );

        $isGeneratedOrderNumber = strpos($orderDescription, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0;
        $isPaymentFinished = MollieStatusUtility::isPaymentFinished($apiPayment->status);

        if (!$isPaymentFinished && $isGeneratedOrderNumber) {
            return $apiPayment;
        }

        switch ($apiPayment->resource) {
            case Config::MOLLIE_API_STATUS_PAYMENT:
                $this->logger->debug(sprintf('%s - Starting to process PAYMENT transaction', self::FILE_NAME));

                $paymentMethod = $this->paymentMethodRepository->getPaymentBy('transaction_id', $apiPayment->id);

                if ($paymentMethod && $apiPayment->mandateId && $paymentMethod['mandate_id'] !== $apiPayment->mandateId) {
                    $this->mollieOrderCreationService->addTransactionMandate($apiPayment->id, $apiPayment->mandateId);
                }

                if ($key !== $apiPayment->metadata->secure_key && $deprecatedKey !== $apiPayment->metadata->secure_key) {
                    throw new TransactionException('Security key is incorrect.', HttpStatusCode::HTTP_UNAUTHORIZED);
                }
                if (!$apiPayment->metadata->cart_id) {
                    throw new TransactionException('Cart id is missing in transaction metadata', HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY);
                }
                if ($apiPayment->hasRefunds()) {
                    if ($isGeneratedOrderNumber) {
                        $this->handlePaymentDescription($apiPayment);
                    }
                    if (isset($apiPayment->amount->value, $apiPayment->amountRefunded->value)
                        && NumberUtility::isLowerOrEqualThan($apiPayment->amount->value, $apiPayment->amountRefunded->value)
                    ) {
                        $this->orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                    } elseif ($apiPayment->amountRefunded->value > 0) {
                        $this->orderStatusService->setOrderStatus($orderId, Config::PARTIAL_REFUND_CODE);
                    }
                } elseif ($this->paymentHasChargedBacks($apiPayment)) {
                    $this->orderStatusService->setOrderStatus($orderId, Config::MOLLIE_CHARGEBACK);
                } else {
                    if (!$orderId && $isPaymentFinished) {
                        $orderId = $this->orderCreationHandler->createOrder($apiPayment, $cart->id);

                        if (!$orderId) {
                            throw new TransactionException('Order is already created', HttpStatusCode::HTTP_METHOD_NOT_ALLOWED);
                        }
                        $this->updatePaymentDescription($apiPayment, $orderId);
                    } elseif (strpos($apiPayment->description, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                        $this->handlePaymentDescription($apiPayment);
                    } elseif ($orderId) {
                        $this->orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    }

                    $orderId = Order::getIdByCartId((int) $apiPayment->metadata->cart_id);
                }
                break;
            case Config::MOLLIE_API_STATUS_ORDER:
                $this->logger->debug(sprintf('%s - Starting to process ORDER transaction', self::FILE_NAME));

                if ($key !== $apiPayment->metadata->secure_key && $deprecatedKey !== $apiPayment->metadata->secure_key) {
                    throw new TransactionException('Security key is incorrect.', HttpStatusCode::HTTP_UNAUTHORIZED);
                }
                if (!$apiPayment->metadata->cart_id) {
                    throw new TransactionException('Cart id is missing in transaction metadata', HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY);
                }

                $isAuthorizablePayment = in_array($apiPayment->method, Config::AUTHORIZABLE_PAYMENTS, false);

                if (!$orderId && $isPaymentFinished) {
                    $orderId = $this->orderCreationHandler->createOrder($apiPayment, $cart->id, $isAuthorizablePayment);

                    if (!$orderId) {
                        throw new TransactionException('Order is already created', HttpStatusCode::HTTP_METHOD_NOT_ALLOWED);
                    }

                    $apiPayment = $this->updateOrderDescription($apiPayment, $orderId);

                    $this->savePaymentStatus($apiPayment->id, $apiPayment->status, $orderId);

                    $order = new Order($orderId);

                    try {
                        $this->shipmentSenderHandler->handleShipmentSender($this->module->getApiClient(), $order, new \OrderState($order->current_state));
                    } catch (ShipmentCannotBeSentException $exception) {
                        $this->logger->error(sprintf('%s - Shipment cannot be sent', self::FILE_NAME), [
                            'exceptions' => ExceptionUtility::getExceptions($exception),
                        ]);
                    } catch (ApiException $exception) {
                        $this->logger->error(sprintf('%s - API exception', self::FILE_NAME), [
                            'exceptions' => ExceptionUtility::getExceptions($exception),
                        ]);
                    }
                } elseif ($apiPayment->amountRefunded) {
                    if (strpos($apiPayment->orderNumber, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                        if (!MollieStatusUtility::isPaymentFinished($apiPayment->status)) {
                            return $apiPayment;
                        }
                        $this->handleOrderDescription($apiPayment);
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
                } elseif ($this->orderHasChargedBacks($apiPayment)) {
                    $this->orderStatusService->setOrderStatus($orderId, Config::MOLLIE_CHARGEBACK);
                } elseif (strpos($apiPayment->orderNumber, OrderNumberUtility::ORDER_NUMBER_PREFIX) === 0) {
                    if ($isPaymentFinished) {
                        $this->handleOrderDescription($apiPayment);
                    }
                } else {
                    $isAuthorizablePaymentInvoiceOnStatusDefault =
                        $this->configurationAdapter->get(Config::MOLLIE_AUTHORIZABLE_PAYMENT_INVOICE_ON_STATUS)
                        === Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_DEFAULT;

                    if (
                        !$isAuthorizablePaymentInvoiceOnStatusDefault
                        && $apiPayment->status === OrderStatus::STATUS_COMPLETED
                        && in_array($apiPayment->method, Config::AUTHORIZABLE_PAYMENTS, true)
                    ) {
                        $this->orderStatusService->setOrderStatus($orderId, Config::MOLLIE_AUTHORIZABLE_PAYMENT_STATUS_SHIPPED);
                    } else {
                        $this->orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    }
                }

                $orderId = Order::getIdByCartId((int) $apiPayment->metadata->cart_id);
        }

        if (!$orderId) {
            return 'Order with given transaction was not found';
        }
        $order = new Order($orderId);

        $this->mollieOrderCreationService->updateMolliePaymentReference($apiPayment->id, $order->reference);

        $this->updateTransaction($orderId, $apiPayment);
        // Store status in database
        $this->savePaymentStatus($apiPayment->id, $apiPayment->status, $orderId);

        $this->logger->debug(sprintf('%s - Processed transaction', self::FILE_NAME));

        return $apiPayment;
    }

    public function updateOrderTransaction($transactionId, $orderReference)
    {
        $transactionInfos = [];
        $isOrder = TransactionUtility::isOrderTransaction($transactionId);
        if ($isOrder) {
            $transaction = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
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
            $transaction = $this->module->getApiClient()->payments->get($transactionId);
            $transactionInfos = $this->getPaymentTransactionInfo($transaction, $transactionInfos);
        }

        $this->updateOrderPayments($transactionInfos, $orderReference);
    }

    /**
     * @param int $orderId
     * @param Payment|MollieOrderAlias $transaction
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
    private function savePaymentStatus($transactionId, $status, $orderId)
    {
        try {
            $result = Db::getInstance()->update(
                'mollie_payments',
                [
                    'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                    'bank_status' => pSQL($status),
                    'order_id' => (int) $orderId,
                ],
                '`transaction_id` = \'' . pSQL($transactionId) . '\''
            );
        } catch (PrestaShopDatabaseException $e) {
            $this->logger->error(sprintf('%s - Could not save Mollie payment status', self::FILE_NAME), [
                'exception' => ExceptionUtility::getExceptions($e),
            ]);

            throw $e;
        }

        $this->logger->debug(sprintf('%s - Payment status saved', self::FILE_NAME), [
            'transasction_id' => $transactionId,
            'status' => $status,
            'order_id' => $orderId,
        ]);

        return $result;
    }

    /**
     * @return array
     */
    private function getVoucherTransactionInfo(Payment $payment, array $transactionInfos)
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
    private function getVoucherRemainderTransactionInfo(Payment $payment, array $transactionInfos)
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
    private function getPaymentTransactionInfo(Payment $payment, array $transactionInfos)
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

    private function updateOrderDescription($apiPayment, int $orderId)
    {
        $environment = (int) $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
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

        return $apiPayment;
    }

    private function updatePaymentDescription(Payment $apiPayment, int $orderId): Payment
    {
        if (!$orderId) {
            $this->logger->debug(sprintf('%s - Order does not exist', self::FILE_NAME), [
                'order_id' => $orderId,
            ]);

            throw new TransactionException('Order does not exist', HttpStatusCode::HTTP_METHOD_NOT_ALLOWED);
        }
        $environment = (int) $this->configurationAdapter->get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($apiPayment->method, $environment);
        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);
        $apiPayment->description = TextGeneratorUtility::generateDescriptionFromCart($paymentMethodObj->description, $orderId);
        $apiPayment->update();

        return $apiPayment;
    }

    private function handlePaymentDescription(Payment $apiPayment)
    {
        $paymentMethod = $this->paymentMethodRepository->getPaymentBy('order_reference', $apiPayment->description);
        if ($paymentMethod) {
            $orderId = Order::getIdByCartId($paymentMethod['cart_id']);
            if (!$orderId) {
                return;
            }
            $apiPayment = $this->updatePaymentDescription($apiPayment, $orderId);
            $this->orderPaymentFeeHandler->addOrderPaymentFee($orderId, $apiPayment);
            $this->processTransaction($apiPayment);
        } else {
            throw new TransactionException('Transaction is no longer used', HttpStatusCode::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    private function handleOrderDescription(MollieOrderAlias $apiPayment)
    {
        $paymentMethod = $this->paymentMethodRepository->getPaymentBy('order_reference', $apiPayment->orderNumber);
        if ($paymentMethod) {
            $orderId = Order::getIdByCartId($paymentMethod['cart_id']);
            if (!$orderId) {
                return;
            }
            $apiPayment = $this->updateOrderDescription($apiPayment, $orderId);
            $this->orderPaymentFeeHandler->addOrderPaymentFee($orderId, $apiPayment);
            $this->processTransaction($apiPayment);
        } else {
            $this->logger->debug(sprintf('%s - Transaction is no longer used', self::FILE_NAME));

            throw new TransactionException('Transaction is no longer used', HttpStatusCode::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    private function orderHasChargedBacks(MollieOrderAlias $apiOrder): bool
    {
        $payments = $apiOrder->payments();
        /** @var Payment $payment */
        foreach ($payments as $payment) {
            if ($payment->hasChargebacks()) {
                return true;
            }
        }

        return false;
    }

    private function paymentHasChargedBacks(Payment $apiPayment): bool
    {
        return $apiPayment->hasChargebacks();
    }
}
