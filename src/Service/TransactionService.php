<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

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
                PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request without proper transaction ID.', Mollie\Config\Config::WARNING);
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

        if (!isset($apiPayment)) {
            $apiPayments = [];
            /** @var MollieOrderAlias $transaction */
            foreach ($transaction->_embedded->payments as $embeddedPayment) {
                $apiPayment = ResourceFactory::createFromApiResult($embeddedPayment, new Payment($this->module->api));
                $apiPayments[] = $apiPayment;
                unset($apiPayment);
            }
            if (count($apiPayments) === 1) {
                $apiPayment = $apiPayments[0];
            } else {
                // In case of multiple payments, the one with the paid status is leading
                foreach ($apiPayments as $payment) {
                    if (in_array($payment->status, [PaymentStatus::STATUS_PAID, PaymentStatus::STATUS_AUTHORIZED])) {
                        $apiPayment = $payment;
                        break;
                    }
                }

                // No paid/authorized payments found, looking for payments with a final status
                if (!isset($apiPayment)) {
                    foreach ($apiPayments as $payment) {
                        if (in_array($payment->status, [
                            PaymentStatus::STATUS_CANCELED,
                            PaymentStatus::STATUS_FAILED,
                            PaymentStatus::STATUS_EXPIRED,
                        ])) {
                            $apiPayment = $payment;
                            break;
                        }
                    }
                }

                // In case there is no final payments, we are going to look for any pending payments
                if (!isset($apiPayment)) {
                    foreach ($apiPayments as $payment) {
                        if (in_array($payment->status, [
                            PaymentStatus::STATUS_PENDING,
                        ])) {
                            $apiPayment = $payment;
                            break;
                        }
                    }
                }
            }
            if (isset($apiPayment)) {
                $apiPayment->metadata = $transaction->metadata;
            }
        }

        if (!isset($apiPayment)) {
            return $this->module->l('Transaction failed', 'webhook');
        }

        $psPayment = $this->paymentMethodRepository->getPaymentBy('transaction_id', $transaction->id);
        $this->setCountryContextIfNotSet($apiPayment);
        $orderId = Order::getOrderByCartId((int)$apiPayment->metadata->cart_id);
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

        if ($apiPayment->metadata->cart_id) {
            if ($apiPayment->hasRefunds() || $apiPayment->hasChargebacks()) {
                if (isset($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
                    && \Mollie\Utility\NumberUtility::isLowerOrEqualThan($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
                ) {
                    $this->orderStatusService->setOrderStatus($orderId, RefundStatus::STATUS_REFUNDED);
                } else {
                    $this->orderStatusService->setOrderStatus($orderId, Mollie\Config\Config::PARTIAL_REFUND_CODE);
                }
            } elseif ($psPayment['method'] === PaymentMethod::BANKTRANSFER
                && $psPayment['bank_status'] === PaymentStatus::STATUS_OPEN
                && $apiPayment->status === PaymentStatus::STATUS_PAID
            ) {
                $order = new Order($orderId);
                $order->payment = isset(Mollie\Config\Config::$methods[$apiPayment->method])
                    ? Mollie\Config\Config::$methods[$apiPayment->method]
                    : $this->module->displayName;
                $order->update();

                $this->orderStatusService->setOrderStatus($orderId, $apiPayment->status);
            } elseif ($psPayment['method'] !== PaymentMethod::BANKTRANSFER
                && ($apiPayment->isPaid() || $apiPayment->isAuthorized() || $apiPayment->isExpired())
                && Tools::encrypt($cart->secure_key) === $apiPayment->metadata->secure_key
            ) {
                $paymentStatus = (int)Mollie\Config\Config::getStatuses()[$apiPayment->status];

                $this->orderStatusService->setOrderStatus($orderId, $paymentStatus);

                $orderId = Order::getOrderByCartId((int)$apiPayment->metadata->cart_id);
            }
        }

        // Store status in database

        $this->saveOrderTransactionData($apiPayment->id, $apiPayment->method, $orderId);

        if (!$this->savePaymentStatus($transaction->id, $apiPayment->status, $orderId)) {
            if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__ . ' said: Could not save Mollie payment status for transaction "' . $transaction->id . '". Reason: ' . Db::getInstance()->getMsgError(), Mollie\Config\Config::WARNING);
            }
        }

        // Log successful webhook requests in extended log mode only
        if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) == Mollie\Config\Config::DEBUG_LOG_ALL) {
            PrestaShopLogger::addLog(__METHOD__ . ' said: Received webhook request for order ' . (int)$orderId . ' / transaction ' . $transaction->id, Mollie\Config\Config::NOTICE);
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
