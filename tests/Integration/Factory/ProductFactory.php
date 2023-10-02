<?php

namespace Mollie\Tests\Integration\Factory;

class ProductFactory implements FactoryInterface
{
    public static function create(array $data = [])
    {
        $product = new \Product(null, false, (int) \Configuration::get('PS_LANG_DEFAULT'));
        $product->id_tax_rules_group = $data['id_tax_rules_group'] ?? 1;
        $product->name = $data['name'] ?? 'test-name';
        $product->description_short = $data['description_short'] ?? 'test-description_short';
        $product->price = $data['price'] ?? 0;
        $product->link_rewrite = \Tools::link_rewrite($product->name);

        $product->save();

        \StockAvailable::setQuantity(
            (int) $product->id,
            0,
            isset($data['quantity']) ? (int) $data['quantity'] : 1
        );

        return $product;
    }
}
