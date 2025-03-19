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

class CartItemWrappingServiceTest extends BaseTestCase
{
    /** @dataProvider dataProvider */
    public function testItAddsWrapping($totalDiscount, $orderLines, $expected)
    {
        $cartItemShippingLineService = new CartItemWrappingService($this->languageService);

        $cart = $this->getMockBuilder(\Cart::class)->setMethods(['getSummaryDetails'])->getMock();

        $cart->method('getSummaryDetails')->willReturn([
            'total_wrapping' => 10,
            'total_wrapping_tax_exc' => 10,
        ]);

        $result = $cartItemShippingLineService->addWrappingLine(10, $cart->getSummaryDetails(), 2, $orderLines);

        $this->assertEquals($expected, $result);
    }

    /** @dataProvider dataProvider */
    public function testItDontAddWrapping($totalDiscount, $orderLines)
    {
        $cartItemShippingLineService = new CartItemWrappingService($this->languageService);

        $cart = $this->getMockBuilder(\Cart::class)->setMethods(['getSummaryDetails'])->getMock();

        $cart->method('getSummaryDetails')->willReturn([
            'total_wrapping' => 0,
            'total_wrapping_tax_exc' => 0,
        ]);

        $result = $cartItemShippingLineService->addWrappingLine(0, $cart->getSummaryDetails(), 2, $orderLines);

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
                    'wrapping' => [
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
