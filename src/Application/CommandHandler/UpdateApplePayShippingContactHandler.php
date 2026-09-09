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
use Carrier;
use Cart;
use Configuration;
use Country;
use Customer;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Mollie\Collector\ApplePayDirect\OrderTotalCollector;
use Mollie\Config\Config;
use Mollie\Exception\GuestCheckoutNotAvailableException;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\OrderPaymentFeeService;
use Mollie\Utility\ApplePayDirect\ShippingMethodUtility;
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

    /**
     * @var OrderPaymentFeeService
     */
    private $orderPaymentFeeService;
    /** @var OrderTotalCollector */
    private $orderTotalCollector;
    /** @var \Mollie */
    private $module;

    public function __construct(
        ApplePayCarriersBuilder $applePayCarriersBuilder,
        OrderPaymentFeeService $orderPaymentFeeService,
        OrderTotalCollector $orderTotalCollector,
        ModuleFactory $module
    ) {
        $this->applePayCarriersBuilder = $applePayCarriersBuilder;
        $this->orderPaymentFeeService = $orderPaymentFeeService;
        $this->orderTotalCollector = $orderTotalCollector;
        $this->module = $module->getModule();
    }

    public function handle(UpdateApplePayShippingContact $command): array
    {
        $cart = new Cart($command->getCartId());
        $customer = $this->getOrCreateCustomer($command->getCustomerId(), $cart);
        $deliveryAddress = $this->getOrCreateAddress($cart->id_address_delivery, $customer->id, $command);
        $invoiceAddress = $this->getOrCreateAddress($cart->id_address_invoice, $customer->id, $command);
        $this->updateCart($cart, $customer, $deliveryAddress->id, $invoiceAddress->id);
        $this->addProductToCart($cart, $command);
        $cart = new Cart($cart->id);
        $this->updateContext($cart, $customer);
        $country = new Country($deliveryAddress->id_country);

        $applePayCarriers = $this->applePayCarriersBuilder->build(Carrier::getCarriersForOrder($country->id_zone), $country->id_zone);

        $shippingMethods = ShippingMethodUtility::collectShippingMethodData($applePayCarriers, $cart);
        $totals = $this->orderTotalCollector->getOrderTotals($applePayCarriers, $cart);

        if (!$totals) {
            return $this->buildUnshippableAddressResponse($cart);
        }

        $paymentFeeData = $this->orderPaymentFeeService->getPaymentFee($totals[0]['amountWithoutFee'], Config::APPLEPAY);
        $paymentFee = $paymentFeeData->getPaymentFeeTaxIncl();

        return [
            'data' => [
                'shipping_methods' => $shippingMethods,
                'totals' => $totals,
                'paymentFee' => [
                    'label' => 'Payment fee',
                    'amount' => $paymentFee,
                    'type' => 'final',
                ],
            ],
            'success' => true,
        ];
    }

    /**
     * No carrier can deliver this cart to the selected zone. The sheet must reject the contact
     * (Apple keeps the Pay button blocked while an addressUnserviceable error is present) and
     * the update dictionary requires a total, so the shipping-less cart total is returned for it.
     */
    private function buildUnshippableAddressResponse(Cart $cart): array
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
                    'message' => $this->module->l('Delivery to this address is not available. Please select a different delivery address.', self::FILE_NAME),
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

    private function addProductToCart(Cart $cart, UpdateApplePayShippingContact $command)
    {
        foreach ($command->getProducts() as $product) {
            $cart->deleteProduct($product->getProductId(), $product->getProductAttribute());
            $quantity = max($product->getWantedQuantity(), 1);
            $cart->updateQty($quantity, $product->getProductId(), $product->getProductAttribute());
        }
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
