<?php

namespace Mollie\Tests\Integration\Factory;

class CartFactory implements FactoryInterface
{
    public static function create(array $data = []): \Cart
    {
        $cart = new \Cart();

        $cart->id_lang = $data['id_lang'] ?? \Configuration::get('PS_LANG_DEFAULT');
        $cart->id_currency = $data['id_currency'] ?? \Configuration::get('PS_CURRENCY_DEFAULT');
        $cart->id_carrier = $data['id_carrier'] ?? CarrierFactory::create()->id;
        $cart->id_address_delivery = $data['id_address_delivery'] ?? AddressFactory::create()->id;
        $cart->id_address_invoice = $data['id_address_invoice'] ?? AddressFactory::create()->id;
        $cart->id_customer = $data['id_customer'] ?? CustomerFactory::create()->id;

        $cart->save();

        \Context::getContext()->cart = $cart;

        return $cart;
    }
}
