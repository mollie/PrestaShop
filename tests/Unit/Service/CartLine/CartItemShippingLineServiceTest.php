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

namespace Mollie\Service\CartLine;

use Mollie\Tests\Unit\BaseTestCase;

class CartItemShippingLineServiceTest extends BaseTestCase
{
    /** @dataProvider dataProvider */
    public function testItAddsDiscount($totalDiscount, $orderLines, $expected)
    {
        $cartItemShippingLineService = new CartItemShippingLineService($this->languageService);

        $cart = $this->getMockBuilder(\Cart::class)->setMethods(['getSummaryDetails'])->getMock();

        $cart->method('getSummaryDetails')->willReturn([
            'total_shipping' => 10,
            'total_shipping_tax_exc' => 10,
        ]);

        $result = $cartItemShippingLineService->addShippingLine(10, $cart->getSummaryDetails(), $orderLines);

        $this->assertEquals($expected, $result);
    }

    /** @dataProvider dataProvider */
    public function testItDontAddDiscount($totalDiscount, $orderLines)
    {
        $cartItemShippingLineService = new CartItemShippingLineService($this->languageService);

        $cart = $this->getMockBuilder(\Cart::class)->setMethods(['getSummaryDetails'])->getMock();

        $cart->method('getSummaryDetails')->willReturn([
            'total_shipping' => 0,
            'total_shipping_tax_exc' => 0,
        ]);

        $result = $cartItemShippingLineService->addShippingLine(0, $cart->getSummaryDetails(), $orderLines);

        $this->assertEquals($orderLines, $result);
    }

    public function dataProvider()
    {
        return [
            'case1' => [
                'totalDiscount' => 10,
                'orderLines' => [
                    'orderLines' => [
                        [
                            'type' => 'product',
                            'name' => 'product_1',
                            'quantity' => 1,
                            'unitPrice' => 10,
                            'totalAmount' => 10,
                            'vatRate' => 0,
                            'vatAmount' => 0,
                        ],
                    ],
                ],
                'expected' => [
                    'orderLines' => [
                        [
                            'type' => 'product',
                            'name' => 'product_1',
                            'quantity' => 1,
                            'unitPrice' => 10,
                            'totalAmount' => 10,
                            'vatRate' => 0,
                            'vatAmount' => 0,
                        ],
                    ],
                    'shipping' => [
                        0 => [
                            'name' => null,
                            'quantity' => 1,
                            'unitPrice' => 10.0,
                            'totalAmount' => 10.0,
                            'vatRate' => 0.0,
                            'vatAmount' => 0.0,
                        ],
                    ],
                ],
            ],
        ];
    }
}
