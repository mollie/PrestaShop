<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Builder\ApplePayDirect\ApplePayProductBuilder;
use Mollie\Handler\Order\OrderCreationHandler;
use Mollie\Utility\NumberUtility;
use PrestaShop\Decimal\DecimalNumber;

class MollieAjaxModuleFrontController extends ModuleFrontController
{
    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'getTotalCartPrice':
                $this->getTotalCartPrice();
            case 'displayCheckoutError':
                $this->displayCheckoutError();
            case 'mollie_apple_pay_validation':
                $this->getApplePaySession(Tools::getValue('validationUrl'));
            case 'mollie_apple_pay_create_order':
                $this->createApplePayOrder();
            case 'mollie_apple_pay_update_shipping_contact':
                die();
            case 'mollie_apple_pay_update_shipping_method':
                $this->updateShippingContact();
        }
    }

    private function getTotalCartPrice()
    {
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

        $paymentFee = new DecimalNumber(Tools::getValue('paymentFee'));
        $orderTotal = new DecimalNumber((string) $cart->getOrderTotal());
        $orderTotalWithFee = NumberUtility::plus($paymentFee->toPrecision(2), $orderTotal->toPrecision(2));

        $orderTotalNoTax = new DecimalNumber((string) $cart->getOrderTotal(false));
        $orderTotalNoTaxWithFee = NumberUtility::plus($paymentFee->toPrecision(2), $orderTotalNoTax->toPrecision(2));

        $total_including_tax = $orderTotalWithFee;
        $total_excluding_tax = $orderTotalNoTaxWithFee;

        $taxConfiguration = new TaxConfiguration();
        $presentedCart = $this->cart_presenter->present($this->context->cart);

        $presentedCart['totals'] = [
            'total' => [
                'type' => 'total',
                'label' => $this->translator->trans('Total', [], 'Shop.Theme.Checkout'),
                'amount' => $taxConfiguration->includeTaxes() ? $total_including_tax : $total_excluding_tax,
                'value' => Tools::displayPrice(
                    $taxConfiguration->includeTaxes() ? (float) $total_including_tax : (float) $total_excluding_tax
                ),
            ],
            'total_including_tax' => [
                'type' => 'total',
                'label' => $this->translator->trans('Total (tax incl.)', [], 'Shop.Theme.Checkout'),
                'amount' => $total_including_tax,
                'value' => Tools::displayPrice((float) $total_including_tax),
            ],
            'total_excluding_tax' => [
                'type' => 'total',
                'label' => $this->translator->trans('Total (tax excl.)', [], 'Shop.Theme.Checkout'),
                'amount' => $total_excluding_tax,
                'value' => Tools::displayPrice((float) $total_excluding_tax),
            ],
        ];

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

    private function displayCheckoutError()
    {
        $errorMessages = explode('#', Tools::getValue('hashTag'));
        foreach ($errorMessages as $errorMessage) {
            if (0 === strpos($errorMessage, 'mollieMessage=')) {
                $errorMessage = str_replace('mollieMessage=', '', $errorMessage);
                $errorMessage = str_replace('_', ' ', $errorMessage);
                $this->context->smarty->assign([
                    'errorMessage' => $errorMessage,
                ]);
                $this->ajaxDie($this->context->smarty->fetch("{$this->module->getLocalPath()}views/templates/front/mollie_error.tpl"));
            }
        }
        $this->ajaxDie();
    }

    private function getApplePaySession(string $validationUrl)
    {
        if (!$this->module->api) {
            die();
        }
        $response = $this->module->api->wallets->requestApplePayPaymentSession($this->getShopUrl(), $validationUrl);
        $this->ajaxDie(json_encode([
            'success' => true,
            'data' => $response
        ]));
    }

    private function createApplePayOrder()
    {
        /** @var ApplePayProductBuilder $applePayProductBuilder */
        $applePayProductBuilder = $this->module->getMollieContainer(ApplePayProductBuilder::class);
        $applePayOrderBuilder = $applePayProductBuilder->build(Tools::getAllValues());

        /** @var OrderCreationHandler $orderCreationHandler */
        $orderCreationHandler = $this->module->getMollieContainer(OrderCreationHandler::class);
        $orderCreationHandler->createApplePayDirectOrder($applePayOrderBuilder);
    }

    //todo: calculate price and send it back to update price
    private function updateShippingContact()
    {
        /** @var ApplePayCarriersBuilder $applePayCarrierBuilder */
        $applePayCarrierBuilder = $this->module->getMollieContainer()->get(ApplePayCarriersBuilder::class);
        $applePayCarriers = $applePayCarrierBuilder->build(Carrier::getCarriers($this->context->language->id, true));

        $this->ajaxDie(json_encode([
            'success' => true,
            'data' => json_encode($applePayCarriers)
        ]));
    }

    public function getShopUrl()
    {
        $shop = $this->context->shop;

        return $shop->domain;
    }
}
