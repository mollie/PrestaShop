<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Configuration;
use Mollie\Config\Config;
use PrestaShop\Decimal\Number;

class CartPriceUtility
{
	/**
	 * Spread the amount evenly.
	 *
	 * @param float $amount
	 * @param int $qty
	 *
	 * @return array Spread amounts
	 *
	 * @since 3.3.3
	 */
	public static function spreadAmountEvenly($amount, $qty)
	{
		if ((int) $qty <= 0) {
			return [];
		}
		// Start with a freshly rounded amount
		$amount = (float) round($amount, Config::API_ROUNDING_PRECISION);
		// Estimate a target spread amount to begin with
		$spreadTotals = array_fill(1, $qty, round($amount / $qty, Config::API_ROUNDING_PRECISION));
		$newTotal = $spreadTotals[1] * $qty;
		// Calculate the difference between applying this amount only and the total amount given
		$difference = abs(round($newTotal - $amount, Config::API_ROUNDING_PRECISION));
		// Start at the last index
		$index = $qty;
		// Keep going until there's no longer a difference
		$difference = new Number((string) $difference);
		$decreaseNumber = new Number('0.01');
		// Keep going until there's no longer a difference
		while ($difference->getPrecision() > 0) {
			// Go for a new pass if there's still a difference after the current one
			$index = $index > 0 ? $index : $qty;
			$difference = $difference->minus($decreaseNumber);
			// Apply the rounding difference at the current index
			$spreadTotals[$index--] += $newTotal < $amount ? 0.01 : -0.01;
		}
		// At the end, compensate for floating point inaccuracy and apply to the last index (points at the lowest amount)
		if (round(abs($amount - array_sum($spreadTotals)), Config::API_ROUNDING_PRECISION) >= 0.01) {
			$spreadTotals[count($spreadTotals) - 1] += 0.01;
		}

		// Group the amounts and return the unit prices at the indices, with the quantities as values
		return array_count_values(array_map('strval', $spreadTotals));
	}

	/**
	 * Check if the rounding mode is supported by the Orders API.
	 *
	 * @return bool
	 *
	 * @since 3.3.2
	 */
	public static function checkRoundingMode()
	{
		return 2 !== (int) Configuration::get('PS_PRICE_ROUND_MODE');
	}
}
