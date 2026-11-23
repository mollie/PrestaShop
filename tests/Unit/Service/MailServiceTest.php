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

namespace Mollie\Tests\Unit\Service;

use Mollie\Adapter\ProductAttributeAdapter;
use Mollie\Adapter\ToolsAdapter;
use Mollie\Factory\ModuleFactory;
use Mollie\Service\MailService;
use Mollie\Subscription\Provider\GeneralSubscriptionMailDataProvider;
use Mollie\Tests\Unit\BaseTestCase;

class MailServiceTest extends BaseTestCase
{
    /**
     * @dataProvider cartRuleListProvider
     */
    public function testCartRuleListIsBuiltFromOrderCartRulesWithoutChangingTheOrder(array $orderCartRules, $isTaxExcluded, array $expected)
    {
        $order = $this->mock(\Order::class);
        $order->method('getCartRules')->willReturn($orderCartRules);
        $order->expects($this->never())->method('addCartRule');

        $tools = $this->mock(ToolsAdapter::class);
        $tools->method('displayPrice')->willReturnCallback(function ($price) {
            return sprintf('€%.2f', $price);
        });

        $mailService = new MailService(
            $this->mock(ModuleFactory::class),
            $this->mock(ProductAttributeAdapter::class),
            $this->mock(GeneralSubscriptionMailDataProvider::class),
            $tools
        );

        $method = new \ReflectionMethod(MailService::class, 'getCartRuleList');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($mailService, $order, $isTaxExcluded));
    }

    public function cartRuleListProvider()
    {
        return [
            'gift and voucher are listed once each' => [
                [
                    ['id_cart_rule' => 9, 'name' => 'Cadeau offert', 'value' => '22.944000', 'value_tax_excl' => '19.120000', 'id_order_invoice' => 13],
                    ['id_cart_rule' => 10, 'name' => 'Bon de bienvenue', 'value' => '5.000000', 'value_tax_excl' => '4.166667', 'id_order_invoice' => 13],
                ],
                false,
                [
                    ['voucher_name' => 'Cadeau offert', 'voucher_reduction' => '-€22.94'],
                    ['voucher_name' => 'Bon de bienvenue', 'voucher_reduction' => '-€5.00'],
                ],
            ],
            'zero value rule has no minus sign' => [
                [
                    ['id_cart_rule' => 3, 'name' => 'Free shipping', 'value' => '0.000000', 'value_tax_excl' => '0.000000', 'id_order_invoice' => 0],
                ],
                false,
                [
                    ['voucher_name' => 'Free shipping', 'voucher_reduction' => '€0.00'],
                ],
            ],
            'tax excluded customer group gets tax excluded amounts' => [
                [
                    ['id_cart_rule' => 9, 'name' => 'Cadeau offert', 'value' => '22.944000', 'value_tax_excl' => '19.120000', 'id_order_invoice' => 13],
                    ['id_cart_rule' => 10, 'name' => 'Bon de bienvenue', 'value' => '5.000000', 'value_tax_excl' => '4.166667', 'id_order_invoice' => 13],
                ],
                true,
                [
                    ['voucher_name' => 'Cadeau offert', 'voucher_reduction' => '-€19.12'],
                    ['voucher_name' => 'Bon de bienvenue', 'voucher_reduction' => '-€4.17'],
                ],
            ],
            'order without cart rules gives an empty list' => [
                [],
                false,
                [],
            ],
        ];
    }

    public function testProductListShowsTheReferenceSavedOnTheOrderLine()
    {
        $data = $this->orderConfData([
            $this->orderLine(1, 'TSHIRT', 'TSHIRT-RED-M'),
            $this->orderLine(2, 'TSHIRT', 'TSHIRT-BLUE-L'),
        ]);

        foreach (['{products}', '{products_txt}'] as $partial) {
            $this->assertStringContainsString('TSHIRT-RED-M', $data[$partial]);
            $this->assertStringContainsString('TSHIRT-BLUE-L', $data[$partial]);
            $this->assertSame(0, preg_match('/TSHIRT(?!-)/', $data[$partial]));
        }
    }

    public function testProductListUnescapesTheSavedReference()
    {
        // OrderDetail::create() escapes the reference twice, so a quote is saved with a backslash
        $data = $this->orderConfData([
            $this->orderLine(1, 'TSHIRT', "O\\'NEILL-01"),
            $this->orderLine(2, 'TSHIRT', null),
        ]);

        foreach (['{products}', '{products_txt}'] as $partial) {
            $this->assertStringContainsString('NEILL-01', $data[$partial]);
            $this->assertStringNotContainsString('\\', $data[$partial]);
            $this->assertSame(0, preg_match('/TSHIRT/', $data[$partial]));
        }
    }

    private function orderConfData(array $orderLines)
    {
        \Configuration::set('PS_MAIL_TYPE', \Mail::TYPE_BOTH);

        $order = $this->mock(\Order::class);
        $order->id_cart = 1;
        $order->method('getProducts')->willReturn($orderLines);
        $order->method('getCustomer')->willReturn(new \Customer());
        $order->method('getCartRules')->willReturn([]);

        $tools = $this->mock(ToolsAdapter::class);
        $tools->method('displayPrice')->willReturn('€10.00');

        $moduleFactory = $this->mock(ModuleFactory::class);
        $moduleFactory->method('getModule')->willReturn($this->mock(\Mollie::class));

        $mailService = new MailService(
            $moduleFactory,
            $this->mock(ProductAttributeAdapter::class),
            $this->mock(GeneralSubscriptionMailDataProvider::class),
            $tools
        );

        $method = new \ReflectionMethod(MailService::class, 'getOrderConfData');
        $method->setAccessible(true);

        return $method->invoke($mailService, $order);
    }

    private function orderLine($productAttributeId, $catalogReference, $orderedReference)
    {
        return [
            'id_product' => 1,
            'product_attribute_id' => $productAttributeId,
            'id_customization' => 0,
            'reference' => $catalogReference,
            'product_reference' => $orderedReference,
            'product_name' => 'T-shirt',
            'product_quantity' => 1,
            'price' => 10,
            'unity' => '',
            'is_virtual' => 0,
        ];
    }
}
