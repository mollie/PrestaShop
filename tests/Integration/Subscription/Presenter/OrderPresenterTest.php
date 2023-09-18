<?php

namespace Mollie\Tests\Integration\Subscription\Presenter;

use Mollie\Subscription\Presenter\OrderPresenter;
use Mollie\Tests\Integration\BaseTestCase;

class OrderPresenterTest extends BaseTestCase
{
    public function testItSuccessfullyPresentsOrder(): void
    {
        $products = [
            [
                'product_attribute_id' => 1,
                'id_product_attribute' => 1,
                'total_price_tax_excl' => 10.00,
                'product_price' => 10.00,
                'total_price' => 10.00,
                'total_price_tax_incl' => 12.10,
                'product_price_wt' => 12.10,
                'total_wt' => 12.10,
                'product_name' => 'test-product-1',
                'product_quantity' => 1,
                'product_id' => 1,
                'id_customization' => 1,
            ],
            [
                'product_attribute_id' => 2,
                'id_product_attribute' => 2,
                'total_price_tax_excl' => 100.00,
                'product_price' => 100.00,
                'total_price' => 100.00,
                'total_price_tax_incl' => 121.00,
                'product_price_wt' => 121.00,
                'total_wt' => 121.00,
                'product_name' => 'test-product-2',
                'product_quantity' => 2,
                'product_id' => 2,
                'id_customization' => 1,
            ],
            [
                'product_attribute_id' => 3,
                'id_product_attribute' => 3,
                'total_price_tax_excl' => 1000.00,
                'product_price' => 1000.00,
                'total_price' => 1000.00,
                'total_price_tax_incl' => 1210.00,
                'product_price_wt' => 1210.00,
                'total_wt' => 1210.00,
                'product_name' => 'test-product-3',
                'product_quantity' => 3,
                'product_id' => 3,
                'id_customization' => 1,
            ],
        ];

        $order = $this->createMock(\Order::class);
        $order->total_paid_tax_excl = 1500;
        $order->id_currency = 1;
        $order->method('getCartProducts')->willReturn($products);
        $order->method('getProducts')->willReturn($products);

        $orderPresenter = new OrderPresenter();

        $result = $orderPresenter->present(
            $order,
            3,
            1300
        );

        $this->assertCount(1, $result->getProducts());
        $this->assertEquals(3, $result->getProducts()[0]['id_product_attribute']);
    }
}
