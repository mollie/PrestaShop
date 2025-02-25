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

namespace Unit\Service\CartLine;

use Mollie\Service\CartLine\CartItemDiscountService;
use Mollie\Service\CartLine\CartItemsService;
use mollie\src\Utility\RoundingUtility;
use Mollie\Tests\Unit\BaseTestCase;

class CartItemDiscountServiceTest extends BaseTestCase
{

    /** @var RoundingUtility */
    public $roundingUtility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roundingUtility = $this->createMock(RoundingUtility::class);

    }

    /**
     * @dataProvider dataProvider
     */
    public function testItAddsDiscount($totalDiscount, $orderLines, $expected)
    {
        $cartItemDiscountService = new CartItemDiscountService($this->roundingUtility);

        $orderLinesResult = $cartItemDiscountService->addDiscountsToProductLines(
            15.00,
            $orderLines,
            15
        );

        $this->assertEquals($orderLinesResult[0]['discount'], $expected);

    }

    public function dataProvider()
    {
        return [
            'case1' => [
                15.0,
                'orderLines' => [
                    'orderLines' => [
                        'name' => 'product name',
                        'sku' => '123465789',
                        'targetVat' => 21.0,
                        'quantity' => 1,
                        'unitPrice' => 10.0,
                        'totalAmount' => 12.1,
                        'category' => 'products',
                        'product_url' => 'https://www.example.com/product.png',
                        'image_url' => 'https://www.example.com/product.png',
                    ],
                ],
                'expected' => [
                    [
                        'name' => 'Discount',
                        'type' => 'discount',
                        'quantity' => 1,
                        'unitPrice' => -15.0,
                        'totalAmount' => -15.0,
                        'targetVat' => 0,
                        'category' => '',
                    ],
                ]
            ]
        ];
    }
}
