<?php
/**
 * Copyright (c) 2012-2019, Mollie B.V.
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
 * Class MollieReturnModuleFrontController
 *
 * @property Context? $context
 * @property Mollie   $module
 */
class MollieReturnModuleFrontController extends ModuleFrontController
{
    const PENDING = 1;
    const DONE = 2;

    /** @var bool $ssl */
    public $ssl = true;
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_left */
    public $display_column_right = false;

    /**
     * Unset the cart id from cookie if the order exists
     *
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function init()
    {
        /** @var Context $context */
        $context = Context::getContext();
        /** @var Cart $cart */
        $cart = new Cart((int) $this->context->cookie->id_cart);
        if (Validate::isLoadedObject($cart) && $cart->orderExists()) {
            unset($context->cart);
            unset($context->cookie->id_cart);
            unset($context->cookie->checkedTOS);
            unset($context->cookie->check_cgv);
        }

        parent::init();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws SmartyException
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     */
    public function initContent()
    {
        if (Tools::getValue('ajax')) {
            $this->processAjax();
            exit;
        }

        parent::initContent();

        $data = array();
        $cart = null;

        /**
         * Set ref is indicative of a payment that is tied to an order instead of a cart, which
         * we still support for transitional reasons.
         */
        if (Tools::getIsset('ref')) {
            $idOrder = (int) Tools::getValue('id');
            // Check if user is allowed to be on the return page
            $data['auth'] = Order::getUniqReferenceOf($idOrder) === Tools::getValue('ref');
            if ($data['auth']) {
                $data['mollie_info'] = Mollie::getPaymentBy('order_id', (int) $idOrder);
            }
        } elseif (Tools::getIsset('cart_id')) {
            $idCart = (int) Tools::getValue('cart_id');

            // Check if user that's seeing this is the cart-owner
            $cart = new Cart($idCart);
            $data['auth'] = (int) $cart->id_customer === $this->context->customer->id;
            if ($data['auth']) {
                $data['mollie_info'] = Mollie::getPaymentBy('cart_id', (int) $idCart);
            }
        }

        if (isset($data['auth']) && $data['auth']) {
            // any paid payments for this cart?

            if ($data['mollie_info'] === false) {
                $data['mollie_info'] = array();
                $data['msg_details'] = $this->module->lang('The order with this id does not exist.');
            } elseif ($data['mollie_info']['method'] === \MollieModule\Mollie\Api\Types\PaymentMethod::BANKTRANSFER
                && $data['mollie_info']['bank_status'] === \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN
            ) {
                $data['msg_details'] = $this->module->lang('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.');
            } else {
                switch ($data['mollie_info']['bank_status']) {
                    case 'created':
                        $data['wait'] = true;
                        break;
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_OPEN:
                        $data['wait'] = true;
                        break;
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PENDING:
                        $data['wait'] = true;
                        break;
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_FAILED:
                        Tools::redirect($this->context->link->getPagelink('order', true, null, array('step' => 3)));
                        break;
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_CANCELED:
                        Tools::redirect($this->context->link->getPagelink('order', true, null, array('step' => 3)));
                        break;
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED:
                        $data['msg_details'] = $this->module->lang('Unfortunately your payment was expired.');
                        break;
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID:
                    case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED:
                        // Validate the Order
                        if (isset($cart) && Validate::isLoadedObject($cart)) {
                            Tools::redirect(
                                $this->context->link->getPageLink(
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
                            );
                        }

                        $data['msg_details'] = $this->module->lang('Thank you. Your order has been received.');
                        break;
                    default:
                        $data['msg_details'] = $this->module->lang('The transaction has an unexpected status.');
                        if (Configuration::get(Mollie::MOLLIE_DEBUG_LOG) == Mollie::DEBUG_LOG_ERRORS) {
                            Logger::addLog(__METHOD__.'said: The transaction has an unexpected status ('.$data['mollie_info']['bank_status'].')', Mollie::WARNING);
                        }
                        break;
                }
            }
        } else {
            // Not allowed? Don't make query but redirect.
            $data['mollie_info'] = array();
            $data['msg_details'] = $this->module->lang('You are not authorised to see this page.');
            Tools::redirect(Context::getContext()->link->getPageLink('index', true));
        }

        $this->context->smarty->assign($data);
        $this->context->smarty->assign('link', $this->context->link);

        if (!empty($data['wait'])) {
            $this->context->smarty->assign(
                'checkStatusEndpoint',
                $this->context->link->getModuleLink(
                    $this->module->name,
                    'return',
                    array('ajax' => 1, 'action' => 'getStatus', 'transaction_id' => $data['mollie_info']['transaction_id']),
                    true
                )
            );
            $this->setTemplate('mollie_wait.tpl');
        } else {
            $this->setTemplate('mollie_return.tpl');
        }
    }

    /**
     * Prepend module path if PS version >= 1.7
     *
     * @param string      $template
     * @param array       $params
     * @param string|null $locale
     *
     * @throws PrestaShopException
     *
     * @since 3.3.2
     */
    public function setTemplate($template, $params = array(), $locale = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $template = "module:mollie/views/templates/front/17_{$template}";
        }

        parent::setTemplate($template, $params, $locale);
    }

    /**
     * Process ajax calls
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     * @throws SmartyException
     *
     * @since 3.3.2
     */
    protected function processAjax()
    {
        if (empty($this->context->customer->id)) {
            return;
        }

        switch (Tools::getValue('action')) {
            case 'getStatus':
                $this->processGetStatus();
                break;
        }

        exit;
    }

    /**
     * Get payment status, can be regularly polled
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws \PrestaShop\PrestaShop\Adapter\CoreException
     * @throws SmartyException
     *
     * @since 3.3.2
     */
    protected function processGetStatus()
    {
        header('Content-Type: application/json;charset=UTF-8');

        $transactionId = Tools::getValue('transaction_id');
        $dbPayment = Mollie::getPaymentBy('transaction_id', $transactionId);
        $cart = new Cart($dbPayment['cart_id']);
        if (!Validate::isLoadedObject($cart)) {
            die(json_encode(array(
                'success' => false,
            )));
        }

        if ((int) $cart->id_customer !== (int) $this->context->customer->id) {
            die(json_encode(array(
                'success' => false,
            )));
        }


        if (!Tools::isSubmit('module')) {
            $_GET['module'] = $this->module->name;
        }
        $webhookController = new MollieWebhookModuleFrontController();
        /** @var \MollieModule\Mollie\Api\Resources\Payment|\MollieModule\Mollie\Api\Resources\Order $apiPayment */
        $apiPayment = $webhookController->processTransaction($transactionId);

        switch ($apiPayment->status) {
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED:
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_FAILED:
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_CANCELED:
                $status = static::DONE;
            die(json_encode(array(
                'success'  => true,
                'status'   => $status,
                'response' => json_encode($apiPayment),
                'href'     => $this->context->link->getPagelink('order', true, null, array('step' => 3))
            )));
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED:
            case \MollieModule\Mollie\Api\Types\PaymentStatus::STATUS_PAID:
                $status = static::DONE;
                break;
            default:
                $status = static::PENDING;
                break;
        }

        die(json_encode(array(
            'success'  => true,
            'status'   => $status,
            'response' => json_encode($apiPayment),
            'href'     => $this->context->link->getPageLink(
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
}
