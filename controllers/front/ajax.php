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

use Mollie\Subscription\Exception\ProductValidationException;
use Mollie\Subscription\Exception\SubscriptionProductValidationException;
use Mollie\Subscription\Validator\CanProductBeAddedToCartValidator;
use Mollie\Utility\NumberUtility;
use PrestaShop\Decimal\DecimalNumber;

class MollieAjaxModuleFrontController extends ModuleFrontController
{
    private const FILE_NAME = 'ajax';

    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'getTotalCartPrice':
                $this->getTotalCartPrice();
                // no break
            case 'displayCheckoutError':
                $this->displayCheckoutError();
                // no break
            case 'validateProduct':
                $this->validateProduct();
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
                'label' => $this->module->l('Total', self::FILE_NAME),
                'amount' => $taxConfiguration->includeTaxes() ? $total_including_tax : $total_excluding_tax,
                'value' => Tools::displayPrice(
                    $taxConfiguration->includeTaxes() ? (float) $total_including_tax : (float) $total_excluding_tax
                ),
            ],
            'total_including_tax' => [
                'type' => 'total',
                'label' => $this->module->l('Total (tax incl.)', self::FILE_NAME),
                'amount' => $total_including_tax,
                'value' => Tools::displayPrice((float) $total_including_tax),
            ],
            'total_excluding_tax' => [
                'type' => 'total',
                'label' => $this->module->l('Total (tax excl.)', self::FILE_NAME),
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

    private function validateProduct()
    {
        /** @var CanProductBeAddedToCartValidator $cartValidation */
        $cartValidation = $this->module->getService(CanProductBeAddedToCartValidator::class);

        $product = Tools::getValue('product');

        $productCanBeAdded = true;
        $message = '';
        try {
            $cartValidation->validate((int) $product['id_product_attribute']);
        } catch (ProductValidationException $e) {
            $productCanBeAdded = false;
            $message = $this->module->l('Product cannot be added because you have subscription product in your cart', self::FILE_NAME);
        } catch (SubscriptionProductValidationException $e) {
            $productCanBeAdded = false;
            $message = $this->module->l('Subscription product cannot be added if you have other products in your cart', self::FILE_NAME);
        }

        $this->ajaxDie(
            json_encode(
                [
                    'success' => true,
                    'isValid' => $productCanBeAdded,
                    'message' => $message,
                ]
            )
        );
    }
}
