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
use Mollie\Utility\OrderRecoverUtility;

class MollieApplePayDirectAjaxModuleFrontController extends ModuleFrontController
{
    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'mollie_apple_pay_validation':
                $this->getApplePaySession();
                // no break
            case 'mollie_apple_pay_update_shipping_contact':
                $this->updateAppleShippingContact();
                // no break
            case 'mollie_apple_pay_update_shipping_method':
                $this->updateShippingMethod();
                // no break
            case 'mollie_apple_pay_create_order':
                $this->createApplePayOrder();
                // no break
            case 'mollie_apple_pay_get_total_price':
                $this->getTotalApplePayCartPrice();
        }
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
        $cartId = (int) Tools::getValue('cartId');
        $customerId = (int) Tools::getValue('customerId');
        if (Tools::getIsset('products')) {
            $products = Tools::getValue('products');
        } else {
            $products = $this->getWantedCartProducts($cartId);
        }

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
        $cartId = (int) Tools::getValue('cartId');
        $cart = new Cart($cartId);

        $products = $this->getWantedCartProducts($cartId);
        /** @var CreateApplePayOrderHandler $handler */
        $handler = $this->module->getMollieContainer(CreateApplePayOrderHandler::class);
        /** @var ApplePayOrderBuilder $applePayProductBuilder */
        $applePayProductBuilder = $this->module->getMollieContainer(ApplePayOrderBuilder::class);

        $shippingContent = Tools::getValue('shippingContact');
        $billingContent = Tools::getValue('billingContact');
        $applePayOrderBuilder = $applePayProductBuilder->build($products, $shippingContent, $billingContent);

        $command = new CreateApplePayOrder(
            $cartId,
            $applePayOrderBuilder,
            json_encode(Tools::getValue('token'))
        );
        $response = $handler->handle($command);
        if (!$response['success']) {
            $this->ajaxDie(json_encode($response));
        }

        //we need to recover created order with customer settings so that we can show order confirmation page
        OrderRecoverUtility::recoverCreatedOrder($this->context, $cart->id_customer);

        $this->ajaxDie(json_encode($response));
    }

    private function getTotalApplePayCartPrice()
    {
        $cartId = Tools::getValue('cartId');
        $cart = new Cart($cartId);

        $this->ajaxDie(json_encode(
            [
                'total' => $cart->getOrderTotal(),
            ]
        ));
    }

    private function getWantedCartProducts(int $cartId)
    {
        $cart = new Cart($cartId);

        $products = [];
        foreach ($cart->getProducts() as $product) {
            $products[] = [
                'id_product' => $product['id_product'],
                'id_product_attribute' => $product['id_product_attribute'],
                'id_customization' => $product['id_customization'],
                'quantity_wanted' => $product['cart_quantity'],
            ];
        }

        return $products;
    }

    private function recoverCreatedOrder(int $customerId)
    {
        // try to move to utility
        $customer = new Customer($customerId);
        $customer->logged = true;
        $this->context->customer = new Customer($customerId);
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
