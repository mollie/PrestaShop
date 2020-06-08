<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use _PhpScoper5eddef0da618a\PrestaShop\Decimal\Number;
use Configuration;
use Mollie\Config\Config;

class CartPriceUtility
{
    /**
     * Spread the amount evenly
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
        // Start with a freshly rounded amount
        $amount = (float)round($amount, Config::API_ROUNDING_PRECISION);
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
     * Check if the rounding mode is supported by the Orders API
     *
     * @return bool
     *
     * @since 3.3.2
     */
    public static function checkRoundingMode()
    {
        return (int)Configuration::get('PS_PRICE_ROUND_MODE') !== 2;
    }
}