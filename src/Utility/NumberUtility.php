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

class NumberUtility
{
    /**
     * Decreases number by its given percentage
     * E.g 75/1.5 = 50
     *
     * @param float $number
     * @param float $percentage
     *
     * @return float
     */
    public static function decreaseByPercentage($number, $percentage)
    {
        if (!$percentage || $percentage <= 0) {
            return $number;
        }
        $numberTransformed = self::toObject($number);
        $totalDecrease = self::toPercentageIncrease($percentage);
        $decrement = (string)$numberTransformed->dividedBy(self::toObject($totalDecrease));

        return (float)$decrement;
    }

    public static function increaseByPercentage($number, $percentage)
    {
        if (!$percentage || $percentage <= 0) {
            return $number;
        }
        $numberTransformed = self::toObject($number);
        $percentageIncrease = self::toPercentageIncrease($percentage);
        $percentageIncreaseTransformed = self::toObject($percentageIncrease);
        $result = (string)$numberTransformed->times($percentageIncreaseTransformed);

        return (float)$result;
    }

    /**
     * E.g 21% will become 1.21
     *
     * @param float $percentage
     *
     * @return float
     */
    public static function toPercentageIncrease($percentage)
    {
        $percentageNumber = self::toObject($percentage);
        $smallerNumber = $percentageNumber->dividedBy(self::toObject(100));
        $result = (string)$smallerNumber->plus(self::toObject(1));

        return (float)$result;
    }


    /**
     * ($a*$b)
     *
     * @param float $a
     * @param float $b
     *
     * @return float
     */
    public static function times($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);
        $result = (string)$firstNumber->times($secondNumber);

        return (float)$result;
    }

    /**
     * ($a/$b)
     *
     * @param float $a
     * @param float $b
     *
     * @return float
     */
    public static function divide($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);
        $result = (string)$firstNumber->dividedBy($secondNumber);

        return (float)$result;
    }

    public static function isEqual($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);

        return $firstNumber->equals($secondNumber);
    }

    public static function isLowerThan($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);

        return $firstNumber->isLowerThan($secondNumber);
    }

    public static function isLowerOrEqualThan($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);

        return $firstNumber->isLowerOrEqualThan($secondNumber);
    }

    public static function minus($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);

        return (float)((string)$firstNumber->minus($secondNumber));
    }

    public static function plus($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);

        return (float)((string)$firstNumber->plus($secondNumber));
    }

    /**
     * @param float $number
     *
     * @return Number
     */
    private static function toObject($number)
    {
        return new Number((string)$number);
    }
}
