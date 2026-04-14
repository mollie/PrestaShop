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

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '8.2.3');
}

use Mollie\DTO\PaymentFeeData;
use Mollie\Service\CartLinesService;
use PHPUnit\Framework\TestCase;

class CompositeRoundingInaccuraciesTest extends TestCase
{
    private function invokeMethod($remaining, $orderLines)
    {
        $service = (new \ReflectionClass(CartLinesService::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(CartLinesService::class, 'compositeRoundingInaccuracies');
        $method->setAccessible(true);

        return $method->invoke($service, $remaining, 2, $orderLines, new PaymentFeeData(false, 0, 0, 0));
    }

    private function sumLines($result)
    {
        $sum = 0;
        foreach ($result as $items) {
            foreach ($items as $item) {
                $sum += $item['totalAmount'];
            }
        }

        return round($sum, 2);
    }

    /**
     * @dataProvider roundingProvider
     */
    public function testCompositeRoundingInaccuracies(
        $remaining,
        $orderLines,
        $expectedSum,
        $expectedGroupCount
    ) {
        $result = $this->invokeMethod($remaining, $orderLines);

        self::assertEquals($expectedSum, $this->sumLines($result));
        self::assertCount($expectedGroupCount, $result);
    }

    public function roundingProvider()
    {
        return [
            'negative remaining with discount — product adjusted, discount untouched' => [
                -0.01,
                [
                    '23¤0¤0' => [
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 12.04, 'totalAmount' => 12.04, 'vatRate' => '20.00', 'vatAmount' => 2.01, 'targetVat' => '20.00', 'categories' => []],
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 12.04, 'totalAmount' => 12.04, 'vatRate' => '20.00', 'vatAmount' => 2.01, 'targetVat' => '20.00', 'categories' => []],
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 12.04, 'totalAmount' => 12.04, 'vatRate' => '20.00', 'vatAmount' => 2.01, 'targetVat' => '20.00', 'categories' => []],
                    ],
                    'discount' => [
                        ['name' => 'Voucher', 'type' => 'discount', 'quantity' => 1, 'unitPrice' => -5.00, 'totalAmount' => -5.00, 'vatRate' => '0.00', 'vatAmount' => 0],
                    ],
                ],
                31.11,
                2,
            ],
            'negative remaining — shipping untouched, product adjusted' => [
                -0.01,
                [
                    '10¤0¤0' => [
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 40.00, 'totalAmount' => 40.00, 'vatRate' => '20.00', 'vatAmount' => 6.67, 'targetVat' => '20.00', 'categories' => []],
                    ],
                    'shipping' => [
                        ['name' => 'Shipping', 'type' => 'shipping_fee', 'quantity' => 1, 'unitPrice' => 5.00, 'totalAmount' => 5.00, 'vatRate' => '20.00', 'vatAmount' => 0.83],
                    ],
                ],
                44.99,
                2,
            ],
            'positive remaining — discount untouched, product adjusted' => [
                0.01,
                [
                    '5¤0¤0' => [
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 20.00, 'totalAmount' => 20.00, 'vatRate' => '20.00', 'vatAmount' => 3.33, 'targetVat' => '20.00', 'categories' => []],
                    ],
                    'discount' => [
                        ['name' => 'Discount', 'type' => 'discount', 'quantity' => 1, 'unitPrice' => -2.00, 'totalAmount' => -2.00, 'vatRate' => '0.00', 'vatAmount' => 0],
                    ],
                ],
                18.01,
                2,
            ],
            'zero remaining — no changes' => [
                0,
                [
                    '1¤0¤0' => [
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 25.00, 'totalAmount' => 25.00, 'vatRate' => '21.00', 'vatAmount' => 4.34, 'targetVat' => '21.00', 'categories' => []],
                    ],
                    'discount' => [
                        ['name' => 'Discount', 'type' => 'discount', 'quantity' => 1, 'unitPrice' => -3.00, 'totalAmount' => -3.00, 'vatRate' => '0.00', 'vatAmount' => 0],
                    ],
                ],
                22.00,
                2,
            ],
            'negative remaining — product only, no discount or shipping' => [
                -0.02,
                [
                    '7¤0¤0' => [
                        ['name' => 'Product', 'type' => 'physical', 'quantity' => 1, 'unitPrice' => 30.00, 'totalAmount' => 30.00, 'vatRate' => '20.00', 'vatAmount' => 5.00, 'targetVat' => '20.00', 'categories' => []],
                    ],
                ],
                29.98,
                1,
            ],
            'negative remaining — digital product adjusted' => [
                -0.01,
                [
                    '3¤0¤0' => [
                        ['name' => 'Digital', 'type' => 'digital', 'quantity' => 1, 'unitPrice' => 9.99, 'totalAmount' => 9.99, 'vatRate' => '20.00', 'vatAmount' => 1.67, 'targetVat' => '20.00', 'categories' => []],
                    ],
                ],
                9.98,
                1,
            ],
        ];
    }
}
