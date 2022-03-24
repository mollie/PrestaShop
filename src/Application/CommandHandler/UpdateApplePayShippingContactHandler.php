<?php

namespace Mollie\Application\CommandHandler;

use Address;
use Carrier;
use Cart;
use Configuration;
use Country;
use Customer;
use Language;
use Mollie\Application\Command\UpdateApplePayShippingContact;
use Mollie\Builder\ApplePayDirect\ApplePayCarriersBuilder;
use Tools;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;

final class UpdateApplePayShippingContactHandler
{
    /**
     * @var ApplePayCarriersBuilder
     */
    private $applePayCarriersBuilder;
    /**
     * @var Language
     */
    private $language;

    public function __construct(
        ApplePayCarriersBuilder $applePayCarriersBuilder,
        Language $language
    ) {
        $this->applePayCarriersBuilder = $applePayCarriersBuilder;
        $this->language = $language;
    }

    public function handle(UpdateApplePayShippingContact $command): array
    {
        $customer = $this->createCustomer($command->getCustomerId());
        $address = $this->createAddress((int) $customer->id, $command);
        $cart = $this->updateCart($customer, $address, $command->getCartId());
        $this->addProductToCart($cart, $command);

        $country = new Country($address->id_country);

        $applePayCarriers = $this->applePayCarriersBuilder->build(Carrier::getCarriersForOrder($this->language->id, true), $country->id_zone);

        $shippingMethods = array_map(function (AppleCarrier $carrier) {
            return [
                'identifier' => $carrier->getCarrierId(),
                'label' => $carrier->getName(),
                'amount' => $carrier->getAmount(),
                'detail' => $carrier->getDelay(),
            ];
        }, $applePayCarriers);

        $totals = array_map(function (AppleCarrier $carrier) use ($cart) {
            return [
                'type' => 'final',
                'label' => $carrier->getName(),
                'amount' => number_format($cart->getOrderTotal(true, Cart::BOTH, null, $carrier->getCarrierId()), 2, '.', ''),
            ];
        }, $applePayCarriers);

        return [
            'data' => [
                'shipping_methods' => $shippingMethods,
                'totals' => $totals,
            ],
            'success' => true
        ];
    }

    private function createAddress(int $customerId, UpdateApplePayShippingContact $command): Address
    {
        $deliveryAddress = new Address();
        $deliveryAddress->address1 = 'ApplePay';
        $deliveryAddress->lastname = 'ApplePay';
        $deliveryAddress->firstname = 'ApplePay';
        $deliveryAddress->id_customer = $customerId;
        $deliveryAddress->alias = 'applePay';
        $deliveryAddress->postcode = $command->getPostalCode();
        $deliveryAddress->id_country = Country::getByIso($command->getCountryCode());
        $deliveryAddress->country = $command->getCountry();
        $deliveryAddress->city = $command->getLocality();
        $deliveryAddress->add();

        return $deliveryAddress;
    }

    private function createCustomer(int $customerId): Customer
    {
        if ($customerId) {
            return new Customer($customerId);
        }
        $customer = new Customer();
        $customer->is_guest = 1;
        $customer->firstname = 'applePay';
        $customer->lastname = 'applePay';
        $customer->email = 'applePay@test.com';
        $customer->passwd = Tools::hash(microtime());
        $customer->add();

        return $customer;
    }

    private function updateCart(Customer $customer, Address $deliveryAddress, int $cartId): cart
    {
        $cart = new Cart($cartId);
        $cart->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $cart->secure_key = $customer->secure_key;
        $cart->id_address_delivery = $deliveryAddress->id;
        $cart->id_address_invoice = $deliveryAddress->id;
        $cart->id_customer = $customer->id;
        $cart->update();

        return $cart;
    }

    private function addProductToCart(Cart $cart, UpdateApplePayShippingContact $command): void
    {
        $cart->deleteProduct($command->getProductId(), $command->getProductAttributeId());
        $cart->updateQty($command->getQuantityWanted(), $command->getProductId(), $command->getProductAttributeId());
    }
}
