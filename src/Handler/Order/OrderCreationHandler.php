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
 *
 * @category   Mollie
 *
 * @see       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Handler\Order;

use Cart;
use Configuration;
use Context;
use Customer;
use Mollie;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentMethod;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Mollie\DTO\Line;
use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Service\OrderFeeService;
use Mollie\Service\OrderStatusService;
use Mollie\Utility\NumberUtility;
use Mollie\Utility\PaymentFeeUtility;
use Mollie\Utility\TextGeneratorUtility;
use MolPaymentMethod;
use Order;
use OrderDetail;
use PrestaShop\Decimal\Number;
use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;
use Tools;
use function DusanKasan\Knapsack\has;

class OrderCreationHandler
{
    /**
     * @var Mollie
     */
    private $module;
    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;
    /**
     * @var OrderStatusService
     */
    private $orderStatusService;
    /**
     * @var OrderFeeService
     */
    private $feeService;
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Mollie $module,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        OrderStatusService $orderStatusService,
        OrderFeeService $feeService,
        Context $context
    ) {
        $this->module = $module;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->orderStatusService = $orderStatusService;
        $this->feeService = $feeService;
        $this->context = $context;
    }

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     */
    public function createOrder($apiPayment, int $cartId, $isKlarnaOrder = false): int
    {
        $orderStatus = $isKlarnaOrder ?
            (int) Config::getStatuses()[PaymentStatus::STATUS_AUTHORIZED] :
            (int) Config::getStatuses()[PaymentStatus::STATUS_PAID];

        $cart = new Cart($cartId);
        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $paymentFee = 0;

        if ($apiPayment->resource === Config::MOLLIE_API_STATUS_PAYMENT) {
            $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
            $paymentMethod = new MolPaymentMethod(
                $this->paymentMethodRepository->getPaymentMethodIdByMethodId($apiPayment->method, $environment)
            );
            $paymentFee = PaymentFeeUtility::getPaymentFee($paymentMethod, $originalAmount);
        } else {
            /** @var Mollie\Api\Resources\OrderLine $line */
            foreach ($apiPayment->lines() as $line) {
                if ($line->sku === Config::PAYMENT_FEE_SKU) {
                    $paymentFee = $line->totalAmount->value;
                }
            }
        }
        if (Order::getOrderByCartId((int) $cartId)) {
            return 0;
        }
        if (!$paymentFee) {
            $this->module->validateOrder(
                (int) $cartId,
                $orderStatus,
                (float) $apiPayment->amount->value,
                isset(Config::$methods[$apiPayment->method]) ? Config::$methods[$apiPayment->method] : $this->module->name,
                null,
                ['transaction_id' => $apiPayment->id],
                null,
                false,
                $cart->secure_key
            );

            /* @phpstan-ignore-next-line */
            $orderId = (int) Order::getOrderByCartId((int) $cartId);

            return $orderId;
        }
        $cartPrice = NumberUtility::plus($originalAmount, $paymentFee);
        $priceDifference = NumberUtility::minus($cartPrice, $apiPayment->amount->value);
        if (abs($priceDifference) > 0.01) {
            if ($apiPayment->resource === Config::MOLLIE_API_STATUS_ORDER) {
                $apiPayment->refundAll();
            } else {
                $apiPayment->refund([
                    'amount' => [
                        'currency' => (string) $apiPayment->amount->currency,
                        'value' => $apiPayment->amount->value,
                    ],
                ]);
            }
            $this->paymentMethodRepository->updatePaymentReason($apiPayment->id, Config::WRONG_AMOUNT_REASON);

            throw new \Exception('Wrong cart amount');
        }

        $this->module->validateOrder(
            (int) $cartId,
            (int) Configuration::get(Mollie\Config\Config::MOLLIE_STATUS_AWAITING),
            (float) $apiPayment->amount->value,
            isset(Config::$methods[$apiPayment->method]) ? Config::$methods[$apiPayment->method] : $this->module->name,
            null,
            ['transaction_id' => $apiPayment->id],
            null,
            false,
            $cart->secure_key
        );

        /* @phpstan-ignore-next-line */
        $orderId = (int) Order::getOrderByCartId((int) $cartId);

        if (PaymentStatus::STATUS_PAID === $apiPayment->status || OrderStatus::STATUS_AUTHORIZED === $apiPayment->status) {
            if ($this->isOrderBackOrder($orderId)) {
                $orderStatus = Config::STATUS_PAID_ON_BACKORDER;
            }
        }

        $this->feeService->createOrderFee($cartId, $paymentFee);

        $order = new Order($orderId);
        $order->total_paid_tax_excl = (float) (new Number((string) $order->total_paid_tax_excl))->plus((new Number((string) $paymentFee)))->toPrecision(2);
        $order->total_paid_tax_incl = (float) (new Number((string) $order->total_paid_tax_incl))->plus((new Number((string) $paymentFee)))->toPrecision(2);
        $order->total_paid = (float) $apiPayment->amount->value;
        $order->total_paid_real = (float) $apiPayment->amount->value;
        $order->update();

        $this->orderStatusService->setOrderStatus($orderId, $orderStatus);

        return Order::getOrderByCartId((int) $cartId);
    }

    /**
     * @param PaymentData|OrderData $paymentData
     * @param Cart $cart
     *
     * @return OrderData|PaymentData
     */
    public function createBankTransferOrder($paymentData, Cart $cart)
    {
        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->module->getMollieContainer(PaymentMethodRepositoryInterface::class);
        $this->module->validateOrder(
            (int) $cart->id,
            (int) Configuration::get(Config::MOLLIE_STATUS_OPEN),
            (float) $paymentData->getAmount()->getValue(),
            isset(Config::$methods[$paymentData->getMethod()]) ? Config::$methods[$paymentData->getMethod()] : $this->module->name,
            null,
            [],
            null,
            false,
            $cart->secure_key
        );

        $orderId = Order::getOrderByCartId($cart->id);
        $order = new Order($orderId);

        $environment = (int) Configuration::get(Mollie\Config\Config::MOLLIE_ENVIRONMENT);
        $paymentMethodId = $this->paymentMethodRepository->getPaymentMethodIdByMethodId($paymentData->getMethod(), $environment);
        $paymentMethodObj = new MolPaymentMethod((int) $paymentMethodId);
        $orderNumber = TextGeneratorUtility::generateDescriptionFromCart($paymentMethodObj->description, $order->id);

        if ($paymentData instanceof PaymentData) {
            $paymentData->setDescription($orderNumber);
        } elseif ($paymentData instanceof OrderData) {
            $paymentData->setOrderNumber($orderNumber);
        }

        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $paymentFee = 0;

        if ($paymentData instanceof PaymentData) {
            $environment = (int) Configuration::get(Config::MOLLIE_ENVIRONMENT);
            $paymentMethod = new MolPaymentMethod(
                $paymentMethodRepository->getPaymentMethodIdByMethodId($paymentData->getMethod(), $environment)
            );
            $paymentFee = PaymentFeeUtility::getPaymentFee($paymentMethod, $originalAmount);
        } else {
            /** @var Line $line */
            foreach ($paymentData->getLines() as $line) {
                if ($line->getSku() === Config::PAYMENT_FEE_SKU) {
                    $paymentFee = $line->getUnitPrice()->getValue();
                }
            }
        }

        if (!$paymentFee) {
            return $paymentData;
        }

        $order->total_paid_tax_excl = (float) (new Number((string) $order->total_paid_tax_excl))->plus((new Number((string) $paymentFee)))->toPrecision(2);
        $order->total_paid_tax_incl = (float) (new Number((string) $order->total_paid_tax_incl))->plus((new Number((string) $paymentFee)))->toPrecision(2);
        $order->total_paid = (float) $paymentData->getAmount()->getValue();
        $order->total_paid_real = (float) $paymentData->getAmount()->getValue();
        $order->update();

        return $paymentData;
    }

    public function createApplePayDirectOrder(\Mollie\DTO\ApplePay\Order $appleOrder)
    {
//        $customerId = Tools::getValue('customerId');
        $customer = $this->createAppleOrderCustomer($appleOrder);
        //todo: check if customization is needed
        $newCart = new Cart();
        $newCart->id_currency = $this->context->currency->id;
        $newCart->id_lang = $this->context->language->id;
        $newCart->secure_key = $customer->secure_key;
        $newCart->id_address_invoice = 1;
        $newCart->save();

        $newCart->updateQty(
            $appleOrder->getProduct()->getWantedQuantity(),
            $appleOrder->getProduct()->getProductId(),
            $appleOrder->getProduct()->getProductAttribute()
        );

        $this->context->cart = $newCart;
        $this->context->customer = $customer;

        $this->module->validateOrder(
            $newCart->id,
            (int) Configuration::get(Config::MOLLIE_STATUS_PAID),
            $newCart->getOrderTotal(),
            PaymentMethod::APPLEPAY,
            null,
            [],
            null,
            false,
            $customer->secure_key
        );
    }

    private function createAppleOrderCustomer(\Mollie\DTO\ApplePay\Order $appleOrder): Customer
    {
        /** @var Hashing $crypto */
        $crypto = ServiceLocator::get(Hashing::class);

        $customer = new Customer();
        $customer->firstname = $appleOrder->getShippingContent()->getGivenName();
        $customer->lastname = $appleOrder->getShippingContent()->getFamilyName();
        $customer->is_guest = true;
        $customer->email = $appleOrder->getShippingContent()->getEmailAddress();
        $customer->passwd = $crypto->hash(microtime(), _COOKIE_KEY_);

        $customer->add();

        return $customer;
    }

    private function isOrderBackOrder($orderId)
    {
        $order = new Order($orderId);
        $orderDetails = $order->getOrderDetailList();
        /** @var OrderDetail $detail */
        foreach ($orderDetails as $detail) {
            $orderDetail = new OrderDetail($detail['id_order_detail']);
            if (
                Configuration::get('PS_STOCK_MANAGEMENT') &&
                ($orderDetail->getStockState() || $orderDetail->product_quantity_in_stock < 0)
            ) {
                return true;
            }
        }

        return false;
    }
}
