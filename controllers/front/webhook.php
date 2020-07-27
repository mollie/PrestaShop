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

use _PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment as MolliePaymentAlias;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Order as MollieOrderAlias;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\ResourceFactory;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentMethod;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\PaymentStatus;
use _PhpScoper5eddef0da618a\Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;
use Mollie\Repository\PaymentMethodRepository;
use Mollie\Service\OrderStatusService;
use Mollie\Utility\OrderStatusUtility;
use Mollie\Utility\TransactionUtility;
use PrestaShop\PrestaShop\Adapter\CoreException;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/../../mollie.php';

/**
 * Class MollieReturnModuleFrontController
 * @method setTemplate
 *
 * @property mixed  context
 * @property Mollie module
 */
class MollieWebhookModuleFrontController extends ModuleFrontController
{
    /** @var bool $ssl */
    public $ssl = true;
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;

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
     * @throws SmartyException
     * @throws CoreException
     * @throws ApiException
     */
    public function initContent()
    {
        if (Configuration::get(Mollie\Config\Config::DEBUG_LOG_ALL)) {
            PrestaShopLogger::addLog('Mollie incoming webhook: '.Tools::file_get_contents('php://input'));
        }

        die($this->executeWebhook());
    }

    /**
     * @return string
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws CoreException
     * @throws ApiException
     */
    protected function executeWebhook()
    {
        if (Tools::getValue('testByMollie')) {
            if (Configuration::get(Mollie\Config\Config::MOLLIE_DEBUG_LOG) >= Mollie\Config\Config::DEBUG_LOG_ERRORS) {
                PrestaShopLogger::addLog(__METHOD__.' said: Mollie webhook tester successfully communicated with the shop.', Mollie\Config\Config::NOTICE);
            }

            return 'OK';
        }

        $transactionId = Tools::getValue('id');
        if (TransactionUtility::isOrderTransaction($transactionId)) {
            $payment = $this->processTransaction($this->module->api->orders->get($transactionId, array('embed' => 'payments')));
        } else {
            $payment = $this->processTransaction($this->module->api->payments->get($transactionId));
        }
        if (is_string($payment)) {
            return $payment;
        }

        return 'OK';
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
                        && $apiPayment->status === \_PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus::STATUS_CREATED
                    ) {
                        $orderPayments = $apiPayment->payments();
                        $paymentStatus = \_PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus::STATUS_CREATED;
                        foreach ($orderPayments as $orderPayment) {
                            $paymentStatus = $orderPayment->status;
                        }
                        $paymentStatus = (int)Mollie\Config\Config::getStatuses()[$paymentStatus];

                        /** @var OrderStatusService $orderStatusService */
                        $orderStatusService = $this->module->getContainer(OrderStatusService::class);
                        $orderStatusService->setOrderStatus($orderId, $paymentStatus);

                        $orderId = Order::getOrderByCartId((int)$apiPayment->metadata->cart_id);
                    } elseif ($psPayment['method'] !== PaymentMethod::BANKTRANSFER
                        && Tools::encrypt($cart->secure_key) === $apiPayment->metadata->secure_key
                    ) {
                        $status = OrderStatusUtility::transformPaymentStatusToRefunded($apiPayment);
                        $paymentStatus = (int) Config::getStatuses()[$status];
                        if ($apiPayment->status === \_PhpScoper5eddef0da618a\Mollie\Api\Types\OrderStatus::STATUS_SHIPPING) {
                            foreach ($apiPayment->shipments() as $shipment) {
                                foreach ($shipment->lines as $line) {
                                    $metaData = json_decode($line->metadata);
                                    $quantity = $line->quantity;
                                    $unitPrice = $line->unitPrice;
                                    $totalAmount = $line->totalAmount;

                                    $molShippedProducts = new MolShippedProducts();
                                    $molShippedProducts->shipment_id = $shipment->id;
                                    $molShippedProducts->mollie_order_id = $line->orderId;
                                    $molShippedProducts->product_id = $metaData->product_id;
                                    $molShippedProducts->quantity = $quantity;
                                    $molShippedProducts->unit_price = $unitPrice->value;
                                    $molShippedProducts->total_amount = $totalAmount->value;
                                    $molShippedProducts->currency = $totalAmount->currency;
                                    $molShippedProducts->order_id = $orderId;

                                    $molShippedProducts->add();
                                }
                                $this->createOrderInvoice($shipment->id, $orderId);
                            }
                        }
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
     * @param int    $orderId
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
        try {
            return Db::getInstance()->update(
                'mollie_payments',
                array(
                    'updated_at'  => array('type' => 'sql', 'value' => 'NOW()'),
                    'bank_status' => pSQL($status),
                    'order_id'    => (int) $orderId,
                ),
                '`transaction_id` = \''.pSQL($transactionId).'\''
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

    public function createOrderInvoice($shipmentId, $orderId)
    {
        /** @var \Mollie\Repository\ShippedProductRepository $shippedProductRepo */
        $shippedProductRepo = $this->module->getContainer(\Mollie\Repository\ShippedProductRepository::class);
        $shippedProducts = $shippedProductRepo->findBy(
            [
                'shipment_id' => $shipmentId
            ]
        );

        $order = new Order($orderId);
        $cart = new Cart($order->id_cart);

        $use_taxes = true;

        $total_method = Cart::BOTH;
//        $invoice_address = new Address((int) $order->{Configuration::get('PS_TAX_ADDRESS_TYPE', null, null, $order->id_shop)});
//        $carrier = new Carrier((int) $order->id_carrier);
//        $tax_calculator = $carrier->getTaxCalculator($invoice_address);

        $total_paid_tax_excl = 0;
        $total_paid_tax_incl = 0;
        $products = [];
        /** @var MolShippedProducts $shippedProduct */
        foreach ($shippedProducts as $shippedProduct) {
            $cartProduct = $cart->getProducts(false, (int) $shippedProduct->product_id)[0];
            $cartProduct['quantity'] = $shippedProduct->quantity;
            $products[] = $cartProduct;
            $total_paid_tax_excl += $shippedProduct->total_amount;
            $total_paid_tax_incl += $shippedProduct->total_amount;
        }
        $total_paid_tax_excl -= $total_paid_tax_excl/100*21;
        $order_invoice = new OrderInvoice();
        $order_invoice->id_order = $orderId;
        $order_invoice->number = Order::getLastInvoiceNumber() + 1;
        $order_invoice->total_paid_tax_excl = Tools::ps_round((float) $total_paid_tax_excl, 2);
        $order_invoice->total_paid_tax_incl = Tools::ps_round((float) $total_paid_tax_incl, 2);
        $order_invoice->total_products = (float) $total_paid_tax_excl;
        $order_invoice->total_products_wt = (float) $total_paid_tax_incl;
        $order_invoice->total_shipping_tax_excl = (float) 0;
        $order_invoice->total_shipping_tax_incl = (float) 0;

        $order_invoice->add();

        $order_detail = new OrderDetail();

        $order_detail->createList($order, $cart, $order->getCurrentOrderState(), $products, (isset($order_invoice) ? $order_invoice->id : 0), $use_taxes, (int) Tools::getValue('add_product_warehouse'));

        foreach ($order->getOrderPayments() as $orderPayment) {
            Db::getInstance()->execute('
                            INSERT INTO `' . _DB_PREFIX_ . 'order_invoice_payment`
                            SET
                                `id_order_invoice` = ' . (int)$order_invoice->id . ',
                                `id_order_payment` = ' . (int)$orderPayment->id . ',
                                `id_order` = ' . (int)$order_invoice->id_order);
        }

        return $order_invoice->number;

    }

}
