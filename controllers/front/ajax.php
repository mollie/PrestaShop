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

use Mollie\Application\Command\CreateApplePayOrder;
use Mollie\Application\Command\RequestApplePayPaymentSession;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Application\Command\UpdateApplePayShippingMethod;
use Mollie\Application\CommandHandler\CreateApplePayOrderHandler;
use Mollie\Application\CommandHandler\RequestApplePayPaymentSessionHandler;
use Mollie\Application\CommandHandler\UpdateApplePayShippingContactHandler;
use Mollie\Application\CommandHandler\UpdateApplePayShippingMethodHandler;
use Mollie\Builder\ApplePayDirect\ApplePayOrderBuilder;
use Mollie\Builder\ApplePayDirect\ApplePayProductBuilder;
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
                $this->getApplePaySession();
            case 'mollie_apple_pay_update_shipping_contact':
                $this->updateAppleShippingContact();
            case 'mollie_apple_pay_update_shipping_method':
                $this->updateShippingMethod();
            case 'mollie_apple_pay_create_order':
                $this->createApplePayOrder();
            case 'mollie_apple_pay_get_total_price':
                $this->getTotalApplePayCartPrice();
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

    private function getApplePaySession()
    {
        $cartId = (int) Tools::getValue('cartId');
        $validationUrl = Tools::getValue('validationUrl');
        /** @var RequestApplePayPaymentSessionHandler $handler */
        $handler = $this->module->getMollieContainer(RequestApplePayPaymentSessionHandler::class);

        $command = new RequestApplePayPaymentSession(
            $validationUrl,
            (int) $this->context->currency->id,
            (int) $this->context->language->id,
            $cartId
        );
        $response = $handler->handle($command);

        $this->ajaxDie(json_encode($response));
    }

    private function updateShippingMethod()
    {
        /** @var UpdateApplePayShippingMethodHandler $handler */
        $handler = $this->module->getMollieContainer(UpdateApplePayShippingMethodHandler::class);
        $shippingMethodDetails = Tools::getValue('shippingMethod');

        $command = new UpdateApplePayShippingMethod(
            (int) $shippingMethodDetails['identifier'],
            (int) Tools::getValue('cartId')
        );
        $response = $handler->handle($command);

        $this->ajaxDie(json_encode($response));
    }

    private function updateAppleShippingContact()
    {
        /** @var UpdateApplePayShippingContactHandler $handler */
        $handler = $this->module->getMollieContainer(UpdateApplePayShippingContactHandler::class);
        /** @var ApplePayProductBuilder $productBuilder */
        $productBuilder = $this->module->getMollieContainer(ApplePayProductBuilder::class);

        $simplifiedContent = Tools::getValue('simplifiedContact');
        $products = Tools::getValue('products');
        $cartId = (int) Tools::getValue('cartId');
        $customerId = (int) Tools::getValue('customerId');

        $command = new UpdateApplePayShippingContact(
            $productBuilder->build($products),
            $cartId,
            $simplifiedContent['postalCode'],
            $simplifiedContent['countryCode'],
            $simplifiedContent['country'],
            $simplifiedContent['locality'],
            $customerId
        );
        $result = $handler->handle($command);

        $this->ajaxDie(json_encode($result));
    }

    private function createApplePayOrder()
    {
        $cartId = (int)  Tools::getValue('cartId');
        $cart = new Cart($cartId);

        /** @var CreateApplePayOrderHandler $handler */
        $handler = $this->module->getMollieContainer(CreateApplePayOrderHandler::class);
        /** @var ApplePayOrderBuilder $applePayProductBuilder */
        $applePayProductBuilder = $this->module->getMollieContainer(ApplePayOrderBuilder::class);

        $applePayOrderBuilder = $applePayProductBuilder->build(Tools::getAllValues());

        $command = new CreateApplePayOrder(
            $cartId,
            $applePayOrderBuilder,
            json_encode(Tools::getValue('token'))
        );
        $response = $handler->handle($command);
        if (!$response['success']) {
            $this->ajaxDie(json_encode($response));
        }

        $this->recoverCreatedOrder($cart->id_customer);

        $this->ajaxDie(json_encode($response));
    }

    private function getTotalApplePayCartPrice()
    {
        $cartId = Tools::getValue('cartId');
        $cart = new Cart($cartId);

        $this->ajaxDie(json_encode(
            [
                'total' => $cart->getOrderTotal()
            ]
        ));
    }

    private function recoverCreatedOrder(int $customerId)
    {
        $customer = new Customer($customerId);
        $customer->logged = 1;
        $this->context->customer = (int) $customerId;
        $this->context->cookie->id_customer = (int) $customerId;
        $this->context->customer = $customer;
        $this->context->cookie->id_customer = (int) $customer->id;
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->logged = 1;
        $this->context->cookie->check_cgv = 1;
        $this->context->cookie->is_guest = $customer->isGuest();
        $this->context->cookie->passwd = $customer->passwd;
        $this->context->cookie->email = $customer->email;
    }
}
