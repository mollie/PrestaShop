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

use MolliePrefix\PrestaShop\Decimal\Number;

class MollieAjaxModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'getTotalCartPrice':
                $cart = Context::getContext()->cart;
                $paymentFee = Tools::getValue('paymentFee');
                if (!$paymentFee) {
                    $presentedCart = $this->cart_presenter->present($this->context->cart);
                    $this->context->smarty->assign([
                        'configuration' => $this->getTemplateVarConfiguration(),
                        'cart' => $presentedCart,
                        'display_transaction_updated_info' => Tools::getIsset('updatedTransaction'),
                    ]);

                    $this->ajaxDie(
                        json_encode(
                            [
                                'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
                            ]
                        )
                    );
                }

                $paymentFee = new Number(Tools::getValue('paymentFee'));
                $orderTotal = new Number((string)$cart->getOrderTotal());
                $orderTotalWithFee = $orderTotal->plus($paymentFee);

                $orderTotalNoTax = new Number((string)$cart->getOrderTotal(false));
                $orderTotalNoTaxWithFee = $orderTotalNoTax->plus($paymentFee);

                $total_including_tax = $orderTotalWithFee->toPrecision(2);
                $total_excluding_tax = $orderTotalNoTaxWithFee->toPrecision(2);

                $taxConfiguration = new TaxConfiguration();
                $presentedCart = $this->cart_presenter->present($this->context->cart);

                $presentedCart['totals'] = array(
                    'total' => array(
                        'type' => 'total',
                        'label' => $this->translator->trans('Total', array(), 'Shop.Theme.Checkout'),
                        'amount' => $taxConfiguration->includeTaxes() ? $total_including_tax : $total_excluding_tax,
                        'value' => Tools::displayPrice(
                            $taxConfiguration->includeTaxes() ? $total_including_tax : $total_excluding_tax
                        ),
                    ),
                    'total_including_tax' => array(
                        'type' => 'total',
                        'label' => $this->translator->trans('Total (tax incl.)', array(), 'Shop.Theme.Checkout'),
                        'amount' => $total_including_tax,
                        'value' => Tools::displayPrice($total_including_tax),
                    ),
                    'total_excluding_tax' => array(
                        'type' => 'total',
                        'label' => $this->translator->trans('Total (tax excl.)', array(), 'Shop.Theme.Checkout'),
                        'amount' => $total_excluding_tax,
                        'value' => Tools::displayPrice($total_excluding_tax),
                    ),
                );

                $this->context->smarty->assign([
                    'configuration' => $this->getTemplateVarConfiguration(),
                    'cart' => $presentedCart,
                    'display_transaction_updated_info' => Tools::getIsset('updatedTransaction'),
                ]);

                $this->ajaxDie(
                    json_encode(
                        [
                            'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
                        ]
                    )
                );
                break;
            case 'displayCheckoutError':
                $errorMessages = explode('#', Tools::getValue('hashTag'));
                foreach ($errorMessages as $errorMessage) {
                    if (strpos($errorMessage, 'mollieMessage=') === 0) {
                        $errorMessage = str_replace('mollieMessage=', '', $errorMessage);
                        $errorMessage = str_replace('_', ' ', $errorMessage);
                        $this->context->smarty->assign([
                            'errorMessage' => $errorMessage
                        ]);
                        $this->ajaxDie($this->context->smarty->fetch("{$this->module->getLocalPath()}views/templates/front/mollie_error.tpl"));
                    }
                }
                $this->ajaxDie();
        }

    }
}
