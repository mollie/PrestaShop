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

namespace Mollie\Application\CommandHandler;

use Address;
use Cache;
use Carrier;
use Cart;
use Configuration;
use Country;
use Customer;
use Mollie\Adapter\Context;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Collector\ApplePayDirect\OrderTotalCollector;
use Mollie\Exception\GuestCheckoutNotAvailableException;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\MinimumPurchaseService;
use Mollie\Utility\ApplePayDirect\ShippingMethodUtility;
use Product;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

final class UpdateApplePayShippingContactHandler
{
    const FILE_NAME = 'UpdateApplePayShippingContactHandler';

    /**
     * @var ApplePayCarriersBuilder
     */
    private $applePayCarriersBuilder;

    /** @var OrderTotalCollector */
    private $orderTotalCollector;
    /** @var \Mollie */
    private $module;
    /** @var MinimumPurchaseService */
    private $minimumPurchaseService;
    /** @var Context */
    private $context;

    public function __construct(
        ApplePayCarriersBuilder $applePayCarriersBuilder,
        OrderTotalCollector $orderTotalCollector,
        ModuleFactory $module,
        MinimumPurchaseService $minimumPurchaseService,
        Context $context
    ) {
        $this->applePayCarriersBuilder = $applePayCarriersBuilder;
        $this->orderTotalCollector = $orderTotalCollector;
        $this->module = $module->getModule();
        $this->minimumPurchaseService = $minimumPurchaseService;
        $this->context = $context;
    }

    public function handle(UpdateApplePayShippingContact $command): array
    {
        $cart = new Cart($command->getCartId());
        $customer = $this->getOrCreateCustomer($command->getCustomerId(), $cart);
        $deliveryAddress = $this->getOrCreateAddress($cart->id_address_delivery, $customer->id, $command);
        $invoiceAddress = $this->getOrCreateAddress($cart->id_address_invoice, $customer->id, $command);
        $this->updateCart($cart, $customer, $deliveryAddress->id, $invoiceAddress->id);
        $productError = $this->addProductToCart($cart, $command);
        $cart = new Cart($cart->id);
        $this->updateContext($cart, $customer);

        $rejection = $productError ?: $this->minimumPurchaseService->getNotReachedMessage($cart);

        if ($rejection) {
            return $this->buildRejectedContactResponse($cart, $rejection);
        }

        $country = new Country($deliveryAddress->id_country);

        $applePayCarriers = $this->applePayCarriersBuilder->build(Carrier::getCarriersForOrder($country->id_zone), $country->id_zone);

        // the sheet preselects the first listed method without firing onshippingmethodselected,
        // so the cart must already carry it or the authorized total diverges from the charged one
        $firstCarrier = $applePayCarriers[0] ?? null;

        if ($firstCarrier) {
            $cart->id_carrier = $firstCarrier->getCarrierId();
            $cart->setDeliveryOption([
                $cart->id_address_delivery => $firstCarrier->getCarrierId() . ',',
            ]);
            $cart->update();
        }

        $shippingMethods = ShippingMethodUtility::collectShippingMethodData($applePayCarriers, $cart);
        $totals = $this->orderTotalCollector->getOrderTotals($applePayCarriers, $cart);

        if (!$totals) {
            return $this->buildRejectedContactResponse(
                $cart,
                $this->module->l('Delivery to this address is not available. Please select a different delivery address.', self::FILE_NAME)
            );
        }

        return [
            'data' => [
                'shipping_methods' => $shippingMethods,
                'totals' => $totals,
            ],
            'success' => true,
        ];
    }

    /**
     * addressUnserviceable is the error Apple keeps the Pay button blocked for, and the update
     * dictionary requires a total, so the shipping-less cart total is returned for it.
     */
    private function buildRejectedContactResponse(Cart $cart, string $message): array
    {
        return [
            'success' => false,
            'data' => [
                'shipping_methods' => [],
                'totals' => [],
                'fallbackTotal' => [
                    'type' => 'final',
                    'label' => Configuration::get('PS_SHOP_NAME'),
                    'amount' => number_format((float) $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), 2, '.', ''),
                ],
            ],
            'errors' => [
                [
                    'code' => 'addressUnserviceable',
                    'contactField' => 'postalAddress',
                    'message' => $message,
                ],
            ],
        ];
    }

    private function getOrCreateAddress(int $existingAddressId, int $customerId, UpdateApplePayShippingContact $command): Address
    {
        if ($existingAddressId) {
            $address = new Address($existingAddressId);
            if ($address->id && $address->alias === 'applePay') {
                $address->postcode = $command->getPostalCode();
                $address->id_country = Country::getByIso($command->getCountryCode());
                $address->country = $command->getCountry();
                $address->city = $command->getLocality();
                $address->id_customer = $customerId;
                $address->deleted = true;
                $address->update();

                // FrontController::init() already priced the cart against this address id, and
                // Address::update() leaves that per-request entry behind, so the quote below would
                // apply the previous country's VAT and shipping tax
                Cache::clean('Address::initialize_' . (int) $address->id);

                return $address;
            }
        }

        $address = new Address();
        $address->address1 = 'ApplePay';
        $address->lastname = 'ApplePay';
        $address->firstname = 'ApplePay';
        $address->id_customer = $customerId;
        $address->alias = 'applePay';
        $address->postcode = $command->getPostalCode();
        $address->id_country = Country::getByIso($command->getCountryCode());
        $address->country = $command->getCountry();
        $address->city = $command->getLocality();
        // Soft-deleted on purpose: this is a temporary Apple Pay sheet address. Keeping it deleted hides it from the customer's address book if the payment is abandoned; real data is written on order creation.
        $address->deleted = true;
        $address->add();

        return $address;
    }

    private function getOrCreateCustomer(int $customerId, Cart $cart): Customer
    {
        if ($customerId) {
            return new Customer($customerId);
        }

        if ($cart->id_customer) {
            return new Customer($cart->id_customer);
        }

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
            throw GuestCheckoutNotAvailableException::guestCheckoutDisabled();
        }

        $customer = new Customer();
        $customer->is_guest = true;
        $customer->firstname = 'applePay';
        $customer->lastname = 'applePay';
        $customer->email = 'applepay-' . (int) $cart->id . '@mollie.com';
        $customer->passwd = Tools::hash(microtime());
        // Soft-deleted on purpose: hides this throwaway guest from the Customers list if the payment is abandoned; it is restored on order creation.
        $customer->deleted = true;
        $customer->add();

        return $customer;
    }

    private function updateCart(Cart $cart, Customer $customer, int $deliveryAddressId, int $invoiceAddressId): void
    {
        $cart->secure_key = $customer->secure_key;
        $cart->id_address_delivery = $deliveryAddressId;
        $cart->id_address_invoice = $invoiceAddressId;
        $cart->id_customer = $customer->id;
        $cart->update();
    }

    /**
     * @return string the reason a product could not be added, empty when every product is in the cart
     */
    private function addProductToCart(Cart $cart, UpdateApplePayShippingContact $command): string
    {
        foreach ($command->getProducts() as $product) {
            $quantity = max($product->getWantedQuantity(), 1);
            $inCart = $cart->getProductQuantity($product->getProductId(), $product->getProductAttribute());

            // deleteProduct() runs CartRule::autoRemoveFromCart() on the emptied cart, which drops
            // vouchers the shopper entered by code, so a cart already holding the line is left alone
            if ((int) ($inCart['quantity'] ?? 0) === $quantity) {
                continue;
            }

            $cart->deleteProduct($product->getProductId(), $product->getProductAttribute());
            /** @var bool|int $added -1 below the minimal quantity, which the PS 1.7 docblock omits */
            $added = $cart->updateQty($quantity, $product->getProductId(), $product->getProductAttribute());

            if ($added !== true) {
                return $this->getProductRefusedMessage($product->getProductId(), $product->getProductAttribute(), $added === -1);
            }
        }

        return '';
    }

    /**
     * Cart::updateQty() returns -1 below the product's minimal quantity and false when the product
     * is out of stock, not available for order or the shop is in catalog mode. The texts are the
     * cart page's own, from the core Shop.Notifications.Error domain.
     */
    private function getProductRefusedMessage(int $productId, int $productAttributeId, bool $belowMinimalQuantity): string
    {
        $productName = (string) Product::getProductName($productId, $productAttributeId ?: null, $this->context->getLanguageId());

        if ($belowMinimalQuantity) {
            $minimalQuantity = $productAttributeId
                ? (int) (new \Combination($productAttributeId))->minimal_quantity
                : (int) (new Product($productId))->minimal_quantity;

            return $this->context->trans(
                'The minimum purchase order quantity for the product %product% is %quantity%.',
                ['%product%' => $productName, '%quantity%' => $minimalQuantity],
                'Shop.Notifications.Error'
            );
        }

        return $this->context->trans(
            'This product (%product%) is no longer available.',
            ['%product%' => $productName],
            'Shop.Notifications.Error'
        );
    }

    private function updateContext(Cart $cart, Customer $customer)
    {
        $context = \Context::getContext();
        $context->cart = $cart;

        $deliveryAddressId = (int) $cart->id_address_delivery;
        $invoiceAddressId = (int) $cart->id_address_invoice;

        $context->updateCustomer($customer);

        $this->restoreCartAddresses($cart, $deliveryAddressId, $invoiceAddressId);
    }

    /**
     * Context::updateCustomer() reassigns both cart addresses to Address::getFirstCustomerAddressId(),
     * which ignores soft-deleted rows and therefore never returns the temporary Apple Pay address:
     * 0 for the throwaway guest, the customer's oldest saved address for a logged-in shopper.
     * Put back the addresses the Apple Pay sheet is being quoted against.
     */
    private function restoreCartAddresses(Cart $cart, int $deliveryAddressId, int $invoiceAddressId): void
    {
        if ((int) $cart->id_address_delivery === $deliveryAddressId && (int) $cart->id_address_invoice === $invoiceAddressId) {
            return;
        }

        // Remaps ps_cart_product rows away from the address the core call just forced onto the cart.
        $cart->updateAddressId((int) $cart->id_address_delivery, $deliveryAddressId);

        $cart->id_address_delivery = $deliveryAddressId;
        $cart->id_address_invoice = $invoiceAddressId;
        $cart->update();
    }
}
