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

namespace Mollie\Handler\ApplePay;

use Address;
use Cart;
use Configuration;
use Country;
use Customer;
use Tools;

class ApplePayDirectCartCreationHandler
{
    public function handle(array $content)
    {
        $customer = $this->createCustomer();
        $address = $this->createAddress($customer->id, $content);

        return $this->createCart($customer, $address);
    }

    private function createAddress($customerId, array $content)
    {
        $deliveryAddress = new Address();
        $deliveryAddress->address1 = 'test';
        $deliveryAddress->lastname = 'test';
        $deliveryAddress->firstname = 'test';
        $deliveryAddress->id_customer = $customerId;
        $deliveryAddress->alias = $customerId;
        $deliveryAddress->postcode = $content['postalCode'];
        $deliveryAddress->id_country = Country::getByIso($content['countryCode']);
        $deliveryAddress->country = $content['country'];
        $deliveryAddress->city = $content['locality'];
        $deliveryAddress->add();

        return $deliveryAddress;
    }

    private function createCustomer()
    {
        $customer = new Customer();
        $customer->is_guest = 1;
        $customer->firstname = 'applePay';
        $customer->lastname = 'applePay';
        $customer->email = 'applePay@test.com';
        $customer->passwd = Tools::hash(microtime());
        $customer->add();

        return $customer;
    }

    private function createCart($customer, $deliveryAddress)
    {
        $cart = new Cart();
        $cart->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $cart->secure_key = $customer->secure_key;
        $cart->id_address_delivery = $deliveryAddress->id;
        $cart->id_address_invoice = $deliveryAddress->id;
        $cart->id_customer = $customer->id;
        $cart->add();

        return $cart;
    }
}
