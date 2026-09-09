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
use Mollie\Controller\AbstractMollieController;
use Mollie\Errors\Http\HttpStatusCode;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Logger\Logger;
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ApplePayDirect\CartOwnershipUtility;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\OrderRecoverUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MollieApplePayDirectAjaxModuleFrontController extends AbstractMollieController
{
    private const FILE_NAME = 'applePayDirectAjax';

    /**
     * Session cookie key holding the cart id the Apple Pay Direct flow is authorized to
     * operate on. Bound server-side in getApplePaySession(); verified on every other action.
     */
    private const APPLE_PAY_CART_ID_COOKIE = 'mollie_apple_pay_cart_id';

    /**
     * Session cookie keys holding the temporary Apple Pay address ids. The addresses are
     * soft-deleted by design, so core's FrontController::init() -> Cart::checkAndUpdateAddresses()
     * zeroes them on the cart at the start of every request whenever the Apple Pay cart is also
     * the session cart. These let each action put the wiped ids back before using the cart.
     */
    private const APPLE_PAY_DELIVERY_ADDRESS_COOKIE = 'mollie_apple_pay_address_delivery';
    private const APPLE_PAY_INVOICE_ADDRESS_COOKIE = 'mollie_apple_pay_address_invoice';
    private const APPLE_PAY_ADDRESS_CART_COOKIE = 'mollie_apple_pay_address_cart';

    /** @var Mollie */
    public $module;

    public function postProcess()
    {
        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - Controller called', self::FILE_NAME));

        $action = Tools::getValue('action');
        switch ($action) {
            case 'mollie_apple_pay_validation':
                $this->getApplePaySession();
                break;
            case 'mollie_apple_pay_update_shipping_contact':
                $this->updateAppleShippingContact();
                break;
            case 'mollie_apple_pay_update_shipping_method':
                $this->updateShippingMethod();
                break;
            case 'mollie_apple_pay_create_order':
                $this->createApplePayOrder();
                break;
            case 'mollie_apple_pay_get_total_price':
                $this->getTotalApplePayCartPrice();
                break;
            case 'mollie_apple_pay_remove_from_cart':
                $this->removeProductFromCart();
                break;
        }

        $logger->debug(sprintf('%s - Controller action ended', self::FILE_NAME));
    }

    private function getApplePaySession()
    {
        $validationUrl = Tools::getValue('validationUrl');

        $cartId = CartOwnershipUtility::resolveReusableCartId(
            (int) Tools::getValue('cartId'),
            (int) $this->context->cart->id
        );

        /** @var RequestApplePayPaymentSessionHandler $handler */
        $handler = $this->module->getService(RequestApplePayPaymentSessionHandler::class);

        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $command = new RequestApplePayPaymentSession(
            $validationUrl,
            (int) $this->context->currency->id,
            (int) $this->context->language->id,
            $cartId
        );

        $response = null;

        try {
            $response = $handler->handle($command);
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Failed to get apple pay session.', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cartId,
                    'validationUrl' => $validationUrl,
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $this->ajaxRender('Unable to get apple pay session');
        }

        // Bind the authorized cart id (session cart or freshly minted cart) to this session
        // so every subsequent Apple Pay Direct action can verify ownership.
        if (is_array($response) && !empty($response['success']) && !empty($response['cartId'])) {
            $this->bindCartToSession((int) $response['cartId']);
        }

        $this->ajaxRender(json_encode($response));
    }

    private function updateShippingMethod()
    {
        if (!$this->isCartBoundToSession((int) Tools::getValue('cartId'))) {
            $this->denyUnboundCart();

            return;
        }

        $this->restoreWipedCartAddresses(new Cart((int) Tools::getValue('cartId')));

        /** @var UpdateApplePayShippingMethodHandler $handler */
        $handler = $this->module->getService(UpdateApplePayShippingMethodHandler::class);

        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $shippingMethodDetails = Tools::getValue('shippingMethod');

        $command = new UpdateApplePayShippingMethod(
            (int) $shippingMethodDetails['identifier'],
            (int) Tools::getValue('cartId')
        );

        $response = null;

        try {
            $response = $handler->handle($command);
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Failed to update shipping method.', self::FILE_NAME), [
                'context' => [
                    'shippingMethodId' => $shippingMethodDetails['identifier'],
                    'cartId' => Tools::getValue('cartId'),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            $this->ajaxRender('Unable to update shipping method');
        }

        $this->ajaxRender(json_encode($response));
    }

    private function updateAppleShippingContact()
    {
        if (!$this->isCartBoundToSession((int) Tools::getValue('cartId'))) {
            $this->denyUnboundCart();

            return;
        }

        $this->restoreWipedCartAddresses(new Cart((int) Tools::getValue('cartId')));

        $originalCartId = (int) $this->context->cookie->id_cart;
        $originalCustomerId = (int) $this->context->cookie->id_customer;

        /** @var UpdateApplePayShippingContactHandler $handler */
        $handler = $this->module->getService(UpdateApplePayShippingContactHandler::class);

        /** @var ApplePayProductBuilder $productBuilder */
        $productBuilder = $this->module->getService(ApplePayProductBuilder::class);

        /** @var Logger $logger * */
        $logger = $this->module->getService(LoggerInterface::class);

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

        try {
            $result = $handler->handle($command);
        } catch (FailedToProvidePaymentFeeException $e) {
            $logger->error(sprintf('%s - Failed to find apple pay address.', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cartId,
                    'customerId' => $customerId,
                ],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            $result = [
                'success' => false,
                'message' => $this->module->l(sprintf('Failed to find address. Please try again. CartId %s', $cartId), self::FILE_NAME),
            ];
        }

        $this->context->cart = new Cart($originalCartId);
        $this->context->cookie->id_cart = $originalCartId;
        $this->context->cookie->id_customer = $originalCustomerId;

        $updatedCart = new Cart($cartId);
        $this->bindCartAddressesToSession(
            $cartId,
            (int) $updatedCart->id_address_delivery,
            (int) $updatedCart->id_address_invoice
        );

        $this->ajaxRender(json_encode($result));
    }

    private function createApplePayOrder()
    {
        $cartId = (int) Tools::getValue('cartId');

        if (!$this->isCartBoundToSession($cartId)) {
            $this->denyUnboundCart();

            return;
        }

        $cart = new Cart($cartId);

        $this->restoreWipedCartAddresses($cart);

        $products = $this->getWantedCartProducts($cartId);
        /** @var CreateApplePayOrderHandler $handler */
        $handler = $this->module->getService(CreateApplePayOrderHandler::class);

        /** @var ApplePayOrderBuilder $applePayProductBuilder */
        $applePayProductBuilder = $this->module->getService(ApplePayOrderBuilder::class);

        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $shippingContent = Tools::getValue('shippingContact');
            $billingContent = Tools::getValue('billingContact');
            $applePayOrderBuilder = $applePayProductBuilder->build($products, $shippingContent, $billingContent);

            $command = new CreateApplePayOrder(
                $cartId,
                $applePayOrderBuilder,
                json_encode(Tools::getValue('token'))
            );
            $response = $handler->handle($command);
        } catch (\Throwable $exception) {
            $logger->error(sprintf('%s - Failed to create apple pay order.', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cartId,
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception),
            ]);

            // Apple Pay only understands a JSON payload here. Letting the exception escape returns an
            // HTML error page, which the sheet reports to the shopper as an opaque parsing failure.
            $this->ajaxRender(json_encode([
                'success' => false,
                'status' => 'STATUS_FAILURE',
                'errors' => [
                    [
                        'code' => 'unknown',
                        'contactField' => null,
                        'message' => $this->module->l('Failed to create the order. Please try again.', self::FILE_NAME),
                    ],
                ],
            ]));

            return;
        }

        if (!$response['success']) {
            $this->ajaxRender(json_encode($response));
        }

        // The created order keeps these address ids; unbind them so a later Apple Pay session
        // cannot restore and then overwrite an address that now belongs to a placed order.
        $this->unbindCartAddressesFromSession();

        //we need to recover created order with customer settings so that we can show order confirmation page
        OrderRecoverUtility::recoverCreatedOrder($this->context, $cart->id_customer);

        $this->ajaxRender(json_encode($response));
    }

    private function getTotalApplePayCartPrice()
    {
        $cartId = (int) Tools::getValue('cartId');

        if (!$this->isCartBoundToSession($cartId)) {
            $this->denyUnboundCart();

            return;
        }

        $cart = new Cart($cartId);

        $this->restoreWipedCartAddresses($cart);

        $this->ajaxRender(json_encode(
            [
                'total' => $cart->getOrderTotal(),
            ]
        ));
    }

    private function removeProductFromCart()
    {
        $cartId = (int) Tools::getValue('cartId');

        if (!$this->isCartBoundToSession($cartId)) {
            $this->denyUnboundCart();

            return;
        }

        $productId = (int) Tools::getValue('id_product');
        $productAttributeId = (int) Tools::getValue('id_product_attribute');

        $cart = new Cart($cartId);

        $this->restoreWipedCartAddresses($cart);

        $cart->deleteProduct($productId, $productAttributeId);

        $this->ajaxRender(json_encode(['success' => true]));
    }

    private function isCartBoundToSession(int $cartId): bool
    {
        return CartOwnershipUtility::isCartAuthorized(
            $cartId,
            (int) $this->context->cart->id,
            (int) $this->context->cookie->{self::APPLE_PAY_CART_ID_COOKIE}
        );
    }

    private function bindCartToSession(int $cartId): void
    {
        $this->context->cookie->{self::APPLE_PAY_CART_ID_COOKIE} = $cartId;
        // Persist now: ajaxRender() outputs the body, after which cookie headers can no longer be sent.
        $this->context->cookie->write();
    }

    private function bindCartAddressesToSession(int $cartId, int $deliveryAddressId, int $invoiceAddressId): void
    {
        if (!$cartId || !$deliveryAddressId || !$invoiceAddressId) {
            return;
        }

        $this->context->cookie->{self::APPLE_PAY_ADDRESS_CART_COOKIE} = $cartId;
        $this->context->cookie->{self::APPLE_PAY_DELIVERY_ADDRESS_COOKIE} = $deliveryAddressId;
        $this->context->cookie->{self::APPLE_PAY_INVOICE_ADDRESS_COOKIE} = $invoiceAddressId;
        // Persist now: ajaxRender() outputs the body, after which cookie headers can no longer be sent.
        $this->context->cookie->write();
    }

    private function unbindCartAddressesFromSession(): void
    {
        $this->context->cookie->{self::APPLE_PAY_ADDRESS_CART_COOKIE} = 0;
        $this->context->cookie->{self::APPLE_PAY_DELIVERY_ADDRESS_COOKIE} = 0;
        $this->context->cookie->{self::APPLE_PAY_INVOICE_ADDRESS_COOKIE} = 0;
        $this->context->cookie->write();
    }

    /**
     * Core wipes the cart's address ids when they point at the soft-deleted Apple Pay
     * addresses (Cart::checkAndUpdateAddresses() during FrontController::init()). Put the
     * bound ids back so quoting and order creation keep using the sheet's addresses.
     */
    private function restoreWipedCartAddresses(Cart $cart): void
    {
        if (!$cart->id || ((int) $cart->id_address_delivery && (int) $cart->id_address_invoice)) {
            return;
        }

        if ((int) $this->context->cookie->{self::APPLE_PAY_ADDRESS_CART_COOKIE} !== (int) $cart->id) {
            return;
        }

        $deliveryAddressId = (int) $this->context->cookie->{self::APPLE_PAY_DELIVERY_ADDRESS_COOKIE};
        $invoiceAddressId = (int) $this->context->cookie->{self::APPLE_PAY_INVOICE_ADDRESS_COOKIE};

        if (!$this->isRestorableApplePayAddress($deliveryAddressId, (int) $cart->id_customer)
            || !$this->isRestorableApplePayAddress($invoiceAddressId, (int) $cart->id_customer)
        ) {
            return;
        }

        $cart->id_address_delivery = $deliveryAddressId;
        $cart->id_address_invoice = $invoiceAddressId;
        $cart->update();
    }

    /**
     * Only a temporary Apple Pay address belonging to the cart's own customer may be
     * restored; anything else coming out of the cookie is treated as tampering. An
     * address already attached to a placed order is refused too, otherwise a later
     * sheet session would rewrite that order's address in place.
     */
    private function isRestorableApplePayAddress(int $addressId, int $cartCustomerId): bool
    {
        if (!$addressId || !$cartCustomerId) {
            return false;
        }

        $address = new Address($addressId);

        return (int) $address->id === $addressId
            && $address->alias === 'applePay'
            && (int) $address->id_customer === $cartCustomerId
            && !$address->isUsed();
    }

    private function denyUnboundCart(): void
    {
        /** @var Logger $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->error(sprintf('%s - Rejected cart operation not bound to the session', self::FILE_NAME), [
            'context' => [
                'cartId' => (int) Tools::getValue('cartId'),
                'action' => Tools::getValue('action'),
            ],
        ]);

        $this->respond(
            'error',
            HttpStatusCode::HTTP_FORBIDDEN,
            $this->module->l('You are not allowed to perform this action on this cart.', self::FILE_NAME)
        );
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
}
