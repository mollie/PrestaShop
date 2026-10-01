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
}
