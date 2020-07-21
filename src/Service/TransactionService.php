<?php

namespace Mollie\Service;

use _PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment as MolliePaymentAlias;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Order as MollieOrderAlias;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\ResourceFactory;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\RefundStatus;
use Address;
use Cart;
use Configuration;
use Context;
use Country;
use Db;
use Mollie;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Utility\TransactionUtility;
use Order;
use OrderPayment;
use PrestaShop\PrestaShop\Adapter\CoreException;
use PrestaShopDatabaseException;
use PrestaShopLogger;
use Tools;

class TransactionService
{

    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var PaymentMethodRepository
     */
    private $paymentMethodRepository;
    /**
     * @var \Mollie\Service\OrderStatusService
     */
    private $orderStatusService;
    /**
     * @var Country
     */
    private $country;

    public function __construct(
        Mollie $module,
        PaymentMethodRepository $paymentMethodRepository,
        OrderStatusService $orderStatusService
    ) {
        $this->module = $module;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderStatusService = $orderStatusService;
        $this->country = Context::getContext()->country;
    }

    /**
     * @param MolliePaymentAlias|MollieOrderAlias $transaction
     *
     * @return string|MolliePaymentAlias Returns a single payment (in case of Orders API it returns the highest prio Payment object) or status string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws CoreException
     * @throws ApiException
     * @since 3.3.0
     * @since 3.3.2 Returns the ApiPayment / ApiOrder instead of OK string, NOT OK/NO ID stays the same
     * @since 3.3.2 Returns the ApiPayment instead of ApiPayment / ApiOrder
     */
    public function processTransaction($transaction)
    {
        if (empty($transaction)) {
            if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__.' said: Received webhook request without proper transaction ID.', Mollie\Config\Config::WARNING);
            }

            return $this->module->l('Transaction failed', 'webhook');
        }

        // Ensure that we are dealing with a Payment object, in case of transaction ID or Payment object w/ Order ID, convert
        if ($transaction instanceof MolliePaymentAlias) {
            if (!empty($transaction->orderId) && TransactionUtility::isOrderTransaction($transaction->orderId)) {
                // Part of order
                $transaction = $this->module->api->orders->get($transaction->orderId, array('embed' => 'payments'));
            } else {
                // Single payment
                $apiPayment = $transaction;
            }
        }

        if (!empty($transaction->id) && TransactionUtility::isOrderTransaction(($transaction->id))) {
            $apiPayment = $this->module->api->orders->get($transaction->id, array('embed' => 'payments'));
        }

        if (!isset($apiPayment)) {
            return $this->module->l('Transaction failed', 'webhook');
        }

        /** @var PaymentMethodRepository $paymentMethodRepo */
        $paymentMethodRepo = $this->module->getContainer(PaymentMethodRepository::class);
        $psPayment = $paymentMethodRepo->getPaymentBy('transaction_id', $transaction->id);
        $this->setCountryContextIfNotSet($apiPayment);
        $orderId = Order::getOrderByCartId((int) $apiPayment->metadata->cart_id);
        /** @var OrderStatusService $orderStatusService */
        $orderStatusService = $this->module->getContainer(OrderStatusService::class);
        $cart = new Cart($apiPayment->metadata->cart_id);

        Db::getInstance()->update(
            'mollie_payments',
            [
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
                'bank_status' => pSQL(\Mollie\Config\Config::getStatuses()[$apiPayment->status]),
                'order_id' => (int)$orderId,
            ],
            '`transaction_id` = \'' . pSQL($transaction->id) . '\''
        );

        switch ($transaction->resource) {
            case Mollie\Config\Config::MOLLIE_API_STATUS_PAYMENT:
                if ($apiPayment->metadata->cart_id) {
                    if ($apiPayment->hasRefunds() || $apiPayment->hasChargebacks()) {
                        if (isset($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
                            && (float)$apiPayment->amountRefunded->value >= (float)$apiPayment->settlementAmount->value
                        ) {
                            $orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                        } else {
                            $orderStatusService->setOrderStatus($orderId, Mollie\Config\Config::PARTIAL_REFUND_CODE);
                        }
                    } elseif ($psPayment['method'] === PaymentMethod::BANKTRANSFER
                    ) {
                        $order = new Order($orderId);
                        $order->payment = isset(Mollie\Config\Config::$methods[$apiPayment->method])
                            ? Mollie\Config\Config::$methods[$apiPayment->method]
                            : $this->module->displayName;
                        $order->update();

                        $orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    } elseif ($psPayment['method'] !== PaymentMethod::BANKTRANSFER
                        && ($apiPayment->isPaid() || $apiPayment->isAuthorized() || $apiPayment->isExpired())
                        && Tools::encrypt($cart->secure_key) === $apiPayment->metadata->secure_key
                    ) {
                        $paymentStatus = (int)Mollie\Config\Config::getStatuses()[$apiPayment->status];

                        /** @var OrderStatusService $orderStatusService */
                        $orderStatusService = $this->module->getContainer(OrderStatusService::class);
                        $orderStatusService->setOrderStatus($orderId, $paymentStatus);

                        $orderId = Order::getOrderByCartId((int)$apiPayment->metadata->cart_id);
                    }
                }
                break;
            case Mollie\Config\Config::MOLLIE_API_STATUS_ORDER:
                if ($apiPayment->metadata->cart_id) {
                    /** todo: investigate if banktransfer logic is needed here */
                    if ($psPayment['method'] === PaymentMethod::BANKTRANSFER
                    ) {
                        $order = new Order($orderId);
                        $order->payment = isset(Mollie\Config\Config::$methods[$apiPayment->method])
                            ? Mollie\Config\Config::$methods[$apiPayment->method]
                            : $this->module->displayName;
                        $order->update();

                        $orderStatusService->setOrderStatus($orderId, $apiPayment->status);
                    } elseif ($psPayment['method'] !== PaymentMethod::BANKTRANSFER
                        && Tools::encrypt($cart->secure_key) === $apiPayment->metadata->secure_key
                    ) {
                        $paymentStatus = (int)Mollie\Config\Config::getStatuses()[$apiPayment->status];

                        /** @var OrderStatusService $orderStatusService */
                        $orderStatusService = $this->module->getContainer(OrderStatusService::class);
                        $orderStatusService->setOrderStatus($orderId, $paymentStatus);

                        $orderId = Order::getOrderByCartId((int)$apiPayment->metadata->cart_id);
                    }
                }
                break;
        }


        // Store status in database

        $this->saveOrderTransactionData($apiPayment->id, $apiPayment->method, $orderId);

        if (!$this->savePaymentStatus($transaction->id, $apiPayment->status, $orderId)) {
            if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__.' said: Could not save Mollie payment status for transaction "'.$transaction->id.'". Reason: '.Db::getInstance()->getMsgError(), Mollie\Config\Config::WARNING);
            }
        }

        // Log successful webhook requests in extended log mode only
        if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) == Mollie\Config\Config::DEBUG_LOG_ALL) {
            PrestaShopLogger::addLog(__METHOD__.' said: Received webhook request for order '.(int) $orderId.' / transaction '.$transaction->id, Mollie\Config\Config::NOTICE);
        }

        return $apiPayment;
    }

    /**
     * Retrieves the OrderPayment object, created at validateOrder. And add transaction data.
     *
     * @param string $molliePaymentId
     * @param string $molliePaymentMethod
     * @param int $orderId
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function saveOrderTransactionData($molliePaymentId, $molliePaymentMethod, $orderId)
    {
        // retrieve ALL payments of order.
        // in the case of a cancel or expired on banktransfer, this will fire too.
        // if no OrderPayment objects is retrieved in the collection, do nothing.
        $order = new Order((int)$orderId);
        $collection = OrderPayment::getByOrderReference($order->reference);
        if (count($collection) > 0) {
            /** @var OrderPayment $orderPayment */
            $orderPayment = $collection[0];

            // for older versions (1.5) , we check if it hasn't been filled yet.
            if (!$orderPayment->transaction_id) {
                $orderPayment->transaction_id = $molliePaymentId;
                $orderPayment->payment_method = $molliePaymentMethod;
                $orderPayment->update();
            }
        }
    }

    /**
     * @param string $transactionId
     * @param int $status
     * @param int $orderId
     *
     * @return bool
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
                    'order_id' => (int)$orderId,
                ],
                '`transaction_id` = \'' . pSQL($transactionId) . '\''
            );
        } catch (PrestaShopDatabaseException $e) {
            /** @var PaymentMethodRepository $paymentMethodRepo */
            $paymentMethodRepo = $this->module->getContainer(PaymentMethodRepository::class);
            $paymentMethodRepo->tryAddOrderReferenceColumn();
            throw $e;
        }
    }

    /**
     * (Re)sets the controller country context.
     * When Prestashop receives a call from Mollie (without context)
     * Prestashop always has default context to fall back on, so context->country
     * is allways Set before executing any controller methods
     *
     * @param MolliePaymentAlias $payment
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws CoreException
     */
    protected function setCountryContextIfNotSet($payment)
    {
        if (empty($this->country) || !$this->country->active) {
            if ($payment->metadata->cart_id) {
                $cart = new Cart((int)$payment->metadata->cart_id);
                if (!empty($cart)) {
                    $address = new Address($cart->id_address_delivery);
                    if (!empty($address)) {
                        $country = new Country($address->id_country);
                        if (!empty($country)) {
                            $this->country = $country;
                        }
                    }
                }
            }
        }
    }
}