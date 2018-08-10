<?php
/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MollieReturnModuleFrontController
 * @method setTemplate
 *
 * @property mixed  context
 * @property Mollie module
 */
class MollieWebhookModuleFrontController extends ModuleFrontController
{
    // @codingStandardsIgnoreStart
    /** @var bool $ssl */
    public $ssl = true;
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;
    // @codingStandardsIgnoreEnd

    /**
     * Prevent displaying the maintenance page
     *
     * @return void
     */
    protected function displayMaintenancePage()
    {
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if (Configuration::get(Mollie::DEBUG_LOG_ALL)) {
            Logger::addLog('Mollie incoming webhook: '.Tools::file_get_contents('php://input'));
        }

        die($this->executeWebhook());
    }

    /**
     * @return string
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function executeWebhook()
    {
        if (Tools::getValue('testByMollie')) {
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: Mollie webhook tester successfully communicated with the shop.', Mollie::NOTICE);
            }

            return 'OK';
        }

        $transactionId = Tools::getValue('id');

        if (empty($transactionId)) {
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: Received webhook request without proper transaction ID.', Mollie::WARNING);
            }

            return 'NO ID';
        }

        try {
            /** @var \Mollie\Api\Resources\Payment $apiPayment */
            $apiPayment = $this->module->api->payments->get($transactionId);
            $transactionId = $apiPayment->id;
        } catch (Exception $e) {
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: Could not retrieve payment details for transaction_id "'.$transactionId.'". Reason: '.$e->getMessage(), Mollie::WARNING);
            }

            return 'NOT OK';
        }

        $psPayment = $this->module->getPaymentBy('transaction_id', $transactionId);

        $this->setCountryContextIfNotSet($apiPayment);

        $orderId = (int) Order::getOrderByCartId($apiPayment->metadata->cart_id);
        $cart = new Cart($apiPayment->metadata->cart_id);
        if ($apiPayment->metadata->cart_id) {
            if ($apiPayment->hasRefunds() || $apiPayment->hasChargebacks()) {
                if (isset($apiPayment->settlementAmount->value, $apiPayment->amountRefunded->value)
                    && (float) $apiPayment->settlementAmount->value - (float) $apiPayment->amountRefunded->value > 0
                ) {
                    $this->module->setOrderStatus($orderId, Mollie::PARTIAL_REFUND_CODE);
                } else {
                    $this->module->setOrderStatus($orderId, \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED);
                }
            } elseif ($psPayment['method'] === 'banktransfer'
                && $psPayment['bank_status'] === \Mollie\Api\Types\PaymentStatus::STATUS_OPEN
                && $apiPayment->status === \Mollie\Api\Types\PaymentStatus::STATUS_PAID
            ) {
                $order = new Order($orderId);
                $order->payment = isset(Mollie::$methods[$apiPayment->method]) ? Mollie::$methods[$apiPayment->method] : $this->module->displayName;
                $order->update();

                $this->module->setOrderStatus($orderId, $apiPayment->status);
            } elseif ($psPayment['method'] !== 'banktransfer'
                && $psPayment['bank_status'] === \Mollie\Api\Types\PaymentStatus::STATUS_OPEN
                && $apiPayment->status === \Mollie\Api\Types\PaymentStatus::STATUS_PAID
                && Tools::encrypt($cart->secure_key) === $apiPayment->metadata->secure_key
            ) {
                $paymentStatus = (int) $this->module->statuses[$apiPayment->status];
                
                if ($paymentStatus < 1) {
                    $paymentStatus = Configuration::get('PS_OS_PAYMENT');
                }
                
                $this->module->validateOrder(
                    (int) $apiPayment->metadata->cart_id,
                    $paymentStatus,
                    $apiPayment->amount->value,
                    isset(Mollie::$methods[$apiPayment->method]) ? Mollie::$methods[$apiPayment->method] : 'Mollie',
                    null,
                    array(),
                    null,
                    false,
                    $cart->secure_key
                );

                $orderId = Order::getOrderByCartId($apiPayment->metadata->cart_id);
            }
        }

        // Store status in database

        $this->saveOrderTransactionData($apiPayment->id, $apiPayment->method, $orderId);

        if (!$this->savePaymentStatus($transactionId, $apiPayment->status, $orderId)) {
            if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                Logger::addLog(__METHOD__.' said: Could not save Mollie payment status for transaction "'.$transactionId.'". Reason: '.Db::getInstance()->getMsgError(), Mollie::WARNING);
            }
        }

        // Log successful webhook requests in extended log mode only
        if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ALL) {
            Logger::addLog(__METHOD__.' said: Received webhook request for order '.(int) $orderId.' / transaction '.$transactionId, Mollie::NOTICE);
        }

        return 'OK';
    }

    /**
     * Retrieves the OrderPayment object, created at validateOrder. And add transaction data.
     *
     * @param string $molliePaymentId
     * @param string $molliePaymentMethod
     * @param int    $orderId
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function saveOrderTransactionData($molliePaymentId, $molliePaymentMethod, $orderId)
    {
        // retrieve ALL payments of order.
        // in the case of a cancel or expired on banktransfer, this will fire too.
        // if no OrderPayment objects is retrieved in the collection, do nothing.
        $order = new Order((int) $orderId);
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
     * @param int    $status
     * @param int    $orderId
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function savePaymentStatus($transactionId, $status, $orderId)
    {
        return Db::getInstance()->update(
            'mollie_payments',
            array(
                'updated_at'  => array('type' => 'sql', 'value' => 'NOW()'),
                'bank_status' => (int) $status,
                'order_id'    => (int) $orderId,
            ),
            '`transaction_id` = \''.pSQL($transactionId).'\''
        );
    }

    /**
     * (Re)sets the controller country context.
     * When Prestashop receives a call from Mollie (without context)
     * Prestashop always has default context to fall back on, so context->country
     * is allways Set before executing any controller methods
     *
     * @param \Mollie\Api\Resources\Payment $payment
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function setCountryContextIfNotSet(\Mollie\Api\Resources\Payment $payment)
    {
        if (empty($this->context->country) || !$this->context->country->active) {
            if ($payment->metadata->cart_id) {
                $cart = new Cart((int) $payment->metadata->cart_id);
                if (!empty($cart)) {
                    $address = new Address($cart->id_address_delivery);
                    if (!empty($address)) {
                        $country = new Country($address->id_country);
                        if (!empty($country)) {
                            $this->context->country = $country;
                        }
                    }
                }
            }
        }
    }
}
