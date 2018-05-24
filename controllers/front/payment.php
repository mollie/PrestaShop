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
    return;
}

/**
 * Class MolliePaymentModuleFrontController
 *
 * @property Context|null $context
 * @property Mollie       $module
 */
class MolliePaymentModuleFrontController extends ModuleFrontController
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
     * @throws \Mollie\Api\Exceptions\ApiException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();
        /** @var Cart $cart */
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);

        if (!$this->validate(
            $cart,
            $customer
        )) {
            $this->errors[] = $this->module->lang['This payment method is not available.'];
            $this->setTemplate('error.tpl');

            return;
        }

        $method = Tools::getValue('method');
        $issuer = Tools::getValue('issuer') ?: null;

        // If no issuer was set yet and the issuer list has its own page, show issuer list here
        if (!$issuer && Configuration::get(Mollie::MOLLIE_ISSUERS) == Mollie::ISSUERS_OWN_PAGE && $method === 'ideal') {
            $tplData = array();
            $issuers = $this->module->getIssuerList();
            $tplData['issuers'] = isset($issuers['ideal']) ? $issuers['ideal'] : array();
            if (!empty($tplData['issuers'])) {
                $tplData['msg_bankselect'] = $this->module->lang['Select your bank:'];
                $tplData['msg_ok'] = $this->module->lang['OK'];
                $tplData['msg_return'] = $this->module->lang['Different payment method'];
                $tplData['link'] = $this->context->link;
                $tplData['cartAmount'] = (int) ($this->context->cart->getOrderTotal(true) * 100);
                $tplData['qrAlign'] = 'center';
                $this->context->smarty->assign($tplData);
                if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                    $this->setTemplate('mollie_issuers.tpl');
                } else {
                    $this->setTemplate('module:mollie/views/templates/front/mollie_issuers17.tpl');
                }

                return;
            }
        }

        // Currency conversion (thou shalt pay in euros)
        $originalAmount = $cart->getOrderTotal(
            true,
            Cart::BOTH
        );
        $amount = $originalAmount;

        // Prepare payment
        $paymentData = Mollie::getPaymentData(
            $amount,
            strtoupper($this->context->currency->iso_code),
            $method,
            $issuer,
            (int) $cart->id,
            $customer->secure_key
        );
        $payment = $this->createPayment($paymentData);

        // Store payment linked to cart
        if ($payment->method != 'banktransfer') {
            Db::getInstance()->insert(
                'mollie_payments',
                array(
                    'cart_id'        => (int) $cart->id,
                    'method'         => $payment->method,
                    'transaction_id' => $payment->id,
                    'bank_status'    => \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                    'created_at'     => date("Y-m-d H:i:s"),
                )
            );
        }

        if ($payment->method == 'banktransfer') {
            $this->module->validateOrder(
                (int) $cart->id,
                $this->module->statuses[$payment->status],
                $originalAmount,
                isset(Mollie::$methods[$payment->method]) ? Mollie::$methods[$payment->method] : 'Mollie',
                null,
                array(),
                null,
                false,
                $customer->secure_key
            );

            $orderId = Order::getOrderByCartId((int) $cart->id);

            Db::getInstance()->insert(
                'mollie_payments',
                array(
                    'cart_id'        => (int) $cart->id,
                    'order_id'       => $orderId,
                    'method'         => $payment->method,
                    'transaction_id' => $payment->id,
                    'bank_status'    => \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                    'created_at'     => date("Y-m-d H:i:s"),
                )
            );
        }

        // Go to payment url
        Tools::redirect($payment->getCheckoutUrl());
    }

    /**
     * Checks if this payment option is still available
     * May redirect the user to a more appropriate page
     *
     * @param Cart     $cart
     * @param Customer $customer
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function validate($cart, $customer)
    {
        if (!$cart->id_customer || !$cart->id_address_delivery || !$cart->id_address_invoice || !$this->module->active) {
            // We be like: how did you even get here?
            Tools::redirect(Context::getContext()->link->getPageLink('index', true));
            return false;
        }

        $authorized = false;

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'mollie') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            return false;
        }

        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $cartId
     *
     * @return string
     * @throws PrestaShopException
     */
    private function generateDescriptionFromCart($cartId)
    {
        $cart = new Cart($cartId);

        $buyer = null;
        if ($cart->id_customer) {
            $buyer = new Customer($cart->id_customer);
        }

        $filters = array(
            'cart.id'            => $cartId,
            'customer.firstname' => $buyer == null ? '' : $buyer->firstname,
            'customer.lastname'  => $buyer == null ? '' : $buyer->lastname,
            'customer.company'   => $buyer == null ? '' : $buyer->company,
        );

        $content = Configuration::get(Mollie::MOLLIE_DESCRIPTION);

        foreach ($filters as $key => $value) {
            $content = str_replace(
                "{".$key."}",
                $value,
                $content
            );
        }

        return $content;
    }

    /**
     * @param array $data
     *
     * @return \Mollie\Api\Resources\Payment|null
     *
     * @throws PrestaShopException
     */
    private function createPayment($data)
    {
        if (Configuration::get(Mollie::MOLLIE_USE_PROFILE_WEBHOOK)) {
            unset($data['webhookUrl']);
        }

        /** @var \Mollie\Api\Resources\Payment $payment */
        $payment = $this->module->api->payments->create($data);

        return $payment;
    }
}
