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
    die('No direct script access');
}

/**
 * Class MollieReturnModuleFrontController
 *
 * @property Context|null $context
 * @property Mollie       $module
 */
class MollieReturnModuleFrontController extends ModuleFrontController
{
    // @codingStandardsIgnoreStart
    /** @var bool $ssl */
    public $ssl = true;
    /** @var bool $display_column_left */
    public $display_column_left = false;
    // @codingStandardsIgnoreEnd

    /**
     * Unset the cart id from cookie if the order exists
     *
     * @throws PrestaShopException
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
     */
    public function initContent()
    {
        parent::initContent();
        $data = array();
        /**
         * Set ref is indicative of a payment that is tied to an order instead of a cart, which
         * we still support for transitional reasons.
         */
        if (Tools::getIsset('ref')) {
            $idOrder = (int) Tools::getValue('id');

            // Check if user is allowed to be on the return page
            $data['auth'] = Order::getUniqReferenceOf($idOrder) === Tools::getValue('ref');
            if ($data['auth']) {
                $data['mollie_info'] = $this->module->getPaymentBy('order_id', (int) $idOrder);
            }
        } elseif (Tools::getIsset('cart_id')) {
            $idCart = (int) Tools::getValue('cart_id');

            // Check if user that's seeing this is the cart-owner
            $cart = new Cart($idCart);
            $data['auth'] = (int) $cart->id_customer === $this->context->customer->id;
            if ($data['auth']) {
                $data['mollie_info'] = $this->module->getPaymentBy('cart_id', (int) $idCart);
            }
        }

        if (isset($data['auth']) && $data['auth']) {
            // any paid payments for this cart?

            if ($data['mollie_info'] === false) {
                $data['mollie_info'] = array();
                $data['msg_details'] = $this->module->lang('The order with this id does not exist.');
            } else {
                switch ($data['mollie_info']['bank_status']) {
                    case Mollie_API_Object_Payment::STATUS_OPEN:
                        $data['msg_details'] = $this->module->lang('We have not received a definite payment status. You will be notified as soon as we receive a confirmation of the bank/merchant.');
                        break;
                    case Mollie_API_Object_Payment::STATUS_CANCELLED:
                        Tools::redirect($this->context->link->getPagelink('order', true, null, array('step' => 3)));
                        break;
                    case Mollie_API_Object_Payment::STATUS_EXPIRED:
                        $data['msg_details'] = $this->module->lang('Unfortunately your payment was expired.');
                        break;
                    case Mollie_API_Object_Payment::STATUS_PAID:
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
                                        'id_order'  => (int) Order::getOrderByCartId($cart->id),
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
        $this->setTemplate('mollie_return.tpl');
    }
}
