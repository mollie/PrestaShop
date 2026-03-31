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

namespace Mollie\Tests\Unit\Utility;

use Mollie\Utility\CartPriceUtility;
use PHPUnit\Framework\TestCase;

class CartPriceUtilityTest extends TestCase
{
    /**
     * @dataProvider spreadAmountEvenlyProvider
     *
     * @param float $amount
     * @param int $qty
     */
    public function testSpreadAmountEvenly($amount, $qty)
    {
        $result = CartPriceUtility::spreadAmountEvenly($amount, $qty);

        if ($qty <= 0) {
            self::assertEmpty($result, 'Zero/negative qty should return empty');

            return;
        }

        // Verify total quantity
        $actualQty = array_sum($result);
        self::assertEquals($qty, $actualQty, "Total quantity should match. Got spread: " . json_encode($result));

        // Verify sum matches amount
        $actualSum = 0;
        foreach ($result as $unitPrice => $count) {
            $actualSum += (float) $unitPrice * $count;
        }
        $actualSum = round($actualSum, 2);
        $expectedAmount = round($amount, 2);
        self::assertEquals($expectedAmount, $actualSum, "Sum of spread amounts ($actualSum) should equal amount ($expectedAmount). Spread: " . json_encode($result));

        // Verify all unit prices differ by at most 0.01
        $prices = array_keys($result);
        if (count($prices) > 1) {
            $minPrice = min(array_map('floatval', $prices));
            $maxPrice = max(array_map('floatval', $prices));
            self::assertLessThanOrEqual(0.01, $maxPrice - $minPrice, 'Unit prices should differ by at most 0.01');
        }
    }

    public function spreadAmountEvenlyProvider()
    {
        return [
            // === Basic cases ===
            'exact even split' => [10.00, 2],
            'exact even split large' => [100.00, 4],
            'single item' => [5.99, 1],

            // === Small rounding differences (fractional cents) ===
            'thirds rounding' => [10.00, 3],
            'sevenths rounding' => [10.00, 7],
            'elevenths rounding' => [1.00, 11],

            // === BUGFIX: Whole number differences (getPrecision was 0) ===
            'whole number diff - 300 qty spread exceeds by 1.00' => [248.00, 300],
            'whole number diff - 150 qty spread exceeds by 1.00' => [100.00, 150],
            'whole number diff - 200 qty spread exceeds by 2.00' => [164.00, 200],
            'whole number diff - 100 qty' => [67.00, 100],

            // === Sub-cent unit prices (high quantity, small price) ===
            'sub-cent 300x0.83 exact match' => [249.00, 300],
            'sub-cent 300x0.83 fractional diff' => [248.94, 300],
            'sub-cent 300x0.83 large diff' => [248.00, 300],
            'sub-cent 500 qty' => [415.00, 500],
            'sub-cent 1000 qty' => [830.00, 1000],

            // === Discount scenarios (smaller amounts after discount) ===
            'small amount after discount' => [3.50, 5],
            'discount leaves odd cents' => [17.43, 12],
            'discount with many items' => [89.97, 150],

            // === Specific price / sale scenarios ===
            'sale price 0.99 x 50' => [49.50, 50],
            'sale price 1.49 x 33' => [49.17, 33],
            'sale price 0.01 x 100' => [1.00, 100],
            'sale price 0.50 x 200' => [100.00, 200],

            // === Edge cases ===
            'zero qty' => [10.00, 0],
            'zero amount' => [0.00, 5],
            'tiny amount' => [0.01, 1],
            'tiny amount many items' => [0.03, 3],
            'large amount' => [9999.99, 1],
            'large qty large amount' => [5000.00, 999],

            // === Currency precision edge cases ===
            'amount that rounds to .X0' => [10.10, 3],
            'amount ending in 5' => [10.05, 2],
            'amount ending in 5 odd qty' => [10.05, 3],

            // === Real-world merchant scenarios ===
            'cheap item bulk: 0.10 x 100' => [10.00, 100],
            'cheap item bulk: 0.15 x 200' => [30.00, 200],
            'wholesale: 2.33 x 144 (gross)' => [335.52, 144],
            'subscription: 9.99 x 12 months' => [119.88, 12],
        ];
    }

    /**
     * @dataProvider spreadAmountConsistencyProvider
     *
     * Verifies that the spread produces consistent results when called multiple times
     */
    public function testSpreadAmountEvenlyIsConsistent($amount, $qty)
    {
        $result1 = CartPriceUtility::spreadAmountEvenly($amount, $qty);
        $result2 = CartPriceUtility::spreadAmountEvenly($amount, $qty);

        self::assertEquals($result1, $result2, 'Repeated calls should produce identical results');
    }

    public function spreadAmountConsistencyProvider()
    {
        return [
            'normal case' => [10.00, 3],
            'sub-cent case' => [248.94, 300],
            'whole number diff' => [248.00, 300],
        ];
    }

    /**
     * Tests that the spread result can be used to build valid Mollie order lines.
     * Each line's totalAmount must equal unitPrice * quantity.
     *
     * @dataProvider mollieOrderLineValidationProvider
     */
    public function testSpreadProducesValidMollieOrderLines($amount, $qty)
    {
        $result = CartPriceUtility::spreadAmountEvenly($amount, $qty);

        $totalOfLines = 0;
        foreach ($result as $unitPrice => $count) {
            $lineTotal = round((float) $unitPrice * $count, 2);
            $totalOfLines += $lineTotal;
        }
        $totalOfLines = round($totalOfLines, 2);
        $expectedAmount = round($amount, 2);

        self::assertEquals(
            $expectedAmount,
            $totalOfLines,
            "Mollie validation: sum of (unitPrice * qty) per line must equal order amount. " .
            "Expected $expectedAmount, got $totalOfLines. Spread: " . json_encode($result)
        );
    }

    public function mollieOrderLineValidationProvider()
    {
        return [
            'bug report exact: 248.94 / 300' => [248.94, 300],
            'bug trigger: 248.00 / 300' => [248.00, 300],
            'normal order' => [59.99, 3],
            'high qty low price' => [50.00, 600],
            'cart rule discount result' => [42.37, 7],
            'percentage discount result' => [76.46, 15],
            'specific price result' => [33.21, 9],
        ];
    }
}
