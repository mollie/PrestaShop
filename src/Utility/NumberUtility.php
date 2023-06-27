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

namespace Mollie\Utility;

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Number;

class NumberUtility
{
    // TODO make all methods consistent: either pass string/float as parameter or cast members to Number/DecimalNumber class beforehand.

    /**
     * @param float $number
     *
     * @return DecimalNumber|Number
     */
    public static function getNumber(float $number)
    {
        if (is_subclass_of(Number::class, DecimalNumber::class)) {
            return new DecimalNumber((string) $number);
        }

        return new Number((string) $number);
    }

    /**
     * Decreases number by its given percentage
     * E.g 75/1.5 = 50.
     *
     * @param float $number
     * @param float $percentage
     *
     * @return float
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function decreaseByPercentage($number, $percentage)
    {
        if (!$percentage || $percentage <= 0) {
            return $number;
        }
        $numberTransformed = self::toObject($number);
        $totalDecrease = self::toPercentageIncrease($percentage);
        $decrement = (string) $numberTransformed->dividedBy(self::toObject($totalDecrease));

        return (float) $decrement;
    }

    public static function increaseByPercentage($number, $percentage)
    {
        if (!$percentage || $percentage <= 0) {
            return $number;
        }
        $numberTransformed = self::toObject($number);
        $percentageIncrease = self::toPercentageIncrease($percentage);
        $percentageIncreaseTransformed = self::toObject($percentageIncrease);
        $result = (string) $numberTransformed->times($percentageIncreaseTransformed);

        return (float) $result;
    }

    /**
     * E.g 21% will become 1.21.
     *
     * @param float $percentage
     *
     * @return float
     */
    public static function toPercentageIncrease($percentage)
    {
        $percentageNumber = self::toObject($percentage);
        $smallerNumber = $percentageNumber->dividedBy(self::toObject(100));
        $result = (string) $smallerNumber->plus(self::toObject(1));

        return (float) $result;
    }

    /**
     * ($a*$b).
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
        $result = (string) $firstNumber->times($secondNumber);

        return (float) $result;
    }

    /**
     * ($a/$b).
     *
     * @param float $a
     * @param float $b
     * @param int $precision
     *
     * @return float
     *
     * @throws \PrestaShop\Decimal\Exception\DivisionByZeroException
     */
    public static function divide($a, $b, $precision = 20)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);
        $result = (string) $firstNumber->dividedBy($secondNumber, $precision);

        return (float) $result;
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

        return (float) ((string) $firstNumber->minus($secondNumber));
    }

    public static function plus($a, $b)
    {
        $firstNumber = self::toObject($a);
        $secondNumber = self::toObject($b);

        return (float) ((string) $firstNumber->plus($secondNumber));
    }

    /**
     * @param float $number
     *
     * @return Number
     */
    private static function toObject($number)
    {
        return new Number((string) $number);
    }
}
