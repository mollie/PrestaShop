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
 * @codingStandardsIgnoreStart
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/../../mollie.php';

/**
 * Class MollieQrcodeModuleFrontController
 *
 * @property Mollie $module
 */
class MollieQrcodeModuleFrontController extends ModuleFrontController
{
    const PENDING = 1;
    const SUCCESS = 2;
    const REFRESH = 3;

    /** @var bool $ssl */
    public $ssl = true;
    /** @var bool If false, does not build left page column content and hides it. */
    public $display_column_left = false;
    /** @var bool If false, does not build right page column content and hides it. */
    public $display_column_right = false;

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     */
    public function initContent()
    {
        if (Tools::getValue('ajax')) {
            $this->processAjax();
            exit;
        }

        if (Tools::getValue('done')) {
            $canceled = true;
            $dbPayment = Mollie::getPaymentBy('cart_id', Tools::getValue('cart_id'));
            if (is_array($dbPayment)) {
                try {
                    /** @var \MollieModule\Mollie\Api\Resources\Payment|\MollieModule\Mollie\Api\Resources\Order $apiPayment
                     */
                    $apiPayment = $this->module->api->{Mollie::selectedApi()}->get($dbPayment['transaction_id']);
                    $canceled = $apiPayment->status !== \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID;
                } catch (\MollieModule\Mollie\Api\Exceptions\ApiException $e) {
                }
            }

            header('Content-Type: text/html');
            $this->context->smarty->assign(array(
                'ideal_logo' => __PS_BASE_URI__.'modules/mollie/views/img/ideal_logo.png',
                'canceled'   => $canceled,
            ));
            echo $this->context->smarty->fetch(_PS_MODULE_DIR_.'mollie/views/templates/front/qr_done.tpl');
            exit;
        }
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     */
    protected function processAjax()
    {
        switch (Tools::getValue('action')) {
            case 'qrCodeNew':
                return $this->processNewQrCode();
            case 'qrCodeStatus':
                return $this->processGetStatus();
            case 'cartAmount':
                return $this->processCartAmount();
        }

        exit;
    }

    /**
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \MollieModule\Mollie\Api\Exceptions\ApiException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    protected function processNewQrCode()
    {
        header('Content-Type: application/json;charset=UTF-8');
        /** @var Mollie $mollie */
        $mollie = Module::getInstanceByName('mollie');
        $context = Context::getContext();
        $customer = $context->customer;
        $cart = $context->cart;
        if (!$cart instanceof Cart || !$cart->getOrderTotal(true)) {
            die(json_encode(array(
                'success' => false,
                'message' => 'No active cart',
            )));
        }

        $orderTotal = $cart->getOrderTotal(true);
        $payment = $mollie->api->{Mollie::selectedApi()}->create(Mollie::getPaymentData(
            $orderTotal,
            Tools::strtoupper($this->context->currency->iso_code),
            \MollieModule\Mollie\Api\Types\PaymentMethod::IDEAL,
            null,
            (int) $cart->id,
            $customer->secure_key,
            true
        ), array(
            'include' => 'details.qrCode',
        ));

        try {
            Db::getInstance()->insert(
                'mollie_payments',
                array(
                    'cart_id'        => (int) $cart->id,
                    'method'         => pSQL($payment->method),
                    'transaction_id' => pSQL($payment->id),
                    'bank_status'    => \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                    'created_at'     => array('type' => 'sql', 'value' => 'NOW()'),
                )
            );
        } catch (PrestaShopDatabaseException $e) {
            Mollie::tryAddOrderReferenceColumn();
            throw $e;
        }

        $src = isset($payment->details->qrCode->src) ? $payment->details->qrCode->src : null;
        die(json_encode(array(
            'success'       => (bool) $src,
            'href'          => $src,
            'idTransaction' => $payment->id,
            'expires'       => strtotime($payment->expiresAt) * 1000,
            'amount'        => (int) ($orderTotal * 100),
        )));
    }

    /**
     * Get payment status, can be regularly polled
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @throws SmartyException
     */
    protected function processGetStatus()
    {
        header('Content-Type: application/json;charset=UTF-8');
        if (empty($this->context->cart)) {
            die(json_encode(array(
                'success' => false,
                'status'  => false,
                'amount'  => null,
            )));
        }

        if (Mollie::isLocalEnvironment()) {
            /** @var \MollieModule\Mollie\Api\Resources\Payment|\MollieModule\Mollie\Api\Resources\Order $payment */
            $apiPayment = $this->module->api->{Mollie::selectedApi()}->get(Tools::getValue('transaction_id'));
            if (!Tools::isSubmit('module')) {
                $_GET['module'] = $this->module->name;
            }
            $webhookController = new MollieWebhookModuleFrontController();
            $webhookController->processTransaction($apiPayment);
        }

        try {
            $payment = Mollie::getPaymentBy('transaction_id', Tools::getValue('transaction_id'));
        } catch (PrestaShopDatabaseException $e) {
            die(json_encode(array(
                'success' => false,
                'status'  => false,
                'amount'  => null,
            )));
        } catch (PrestaShopException $e) {
            die(json_encode(array(
                'success' => false,
                'status'  => false,
                'amount'  => null,
            )));
        }

        switch ($payment['bank_status']) {
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID:
                $status = static::SUCCESS;
                break;
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN:
                $status = static::PENDING;
                break;
            default:
                $status = static::REFRESH;
                break;
        }

        $cart = new Cart($payment['cart_id']);
        $amount = (int) ($cart->getOrderTotal(true) * 100);
        die(json_encode(array(
            'success' => true,
            'status'  => $status,
            'amount'  => $amount,
            'href'    => $this->context->link->getPageLink(
                'order-confirmation',
                true,
                null,
                array(
                    'id_cart'   => (int) $cart->id,
                    'id_module' => (int) $this->module->id,
                    'id_order'  => (int) version_compare(_PS_VERSION_, '1.7.1.0', '>=')
                        ? Order::getIdByCartId((int) $cart->id)
                        : Order::getOrderByCartId((int) $cart->id),
                    'key'       => $cart->secure_key,
                )
            )
        )));
    }

    /**
     * Get the cart amount
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    protected function processCartAmount()
    {
        header('Content-Type: application/json;charset=UTF-8');
        /** @var Context $context */
        $context = Context::getContext();
        /** @var Cart $cart */
        $cart = $context->cart;
        if (!$cart) {
            die(json_encode(array(
                'success' => true,
                'amount'  => 0
            )));
        }

        $cartTotal = (int) ($cart->getOrderTotal(true) * 100);
        die(json_encode(array(
            'success' => true,
            'amount'  => $cartTotal,
        )));
    }
}
