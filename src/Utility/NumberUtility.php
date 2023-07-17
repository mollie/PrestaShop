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
use PrestaShop\Decimal\Operation\Rounding;

class NumberUtility
{
    private const DECIMAL_PRECISION = 2;
    private const FLOAT_PRECISION = 6;
    private const ROUNDING = Rounding::ROUND_HALF_UP;

    // TODO make all methods consistent: either pass string/float as parameter or cast members to Number/DecimalNumber class beforehand.

    public static function toPrecision(
        float $number,
        int $precision = self::DECIMAL_PRECISION,
        string $roundingMode = self::ROUNDING
    ): float {
        $decimalNumber = self::getNumber($number);

        return (float) $decimalNumber->toPrecision($precision, $roundingMode);
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
        $numberTransformed = self::getNumber($number);
        $totalDecrease = self::toPercentageIncrease($percentage);
        $decrement = (string) $numberTransformed->dividedBy(self::getNumber($totalDecrease));

        return (float) $decrement;
    }

    public static function increaseByPercentage($number, $percentage)
    {
        if (!$percentage || $percentage <= 0) {
            return $number;
        }
        $numberTransformed = self::getNumber($number);
        $percentageIncrease = self::toPercentageIncrease($percentage);
        $percentageIncreaseTransformed = self::getNumber($percentageIncrease);
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
        $percentageNumber = self::getNumber($percentage);
        $smallerNumber = $percentageNumber->dividedBy(self::getNumber(100));
        $result = (string) $smallerNumber->plus(self::getNumber(1));

        return (float) $result;
    }

    public static function times(
        float $target,
        float $factor,
        int $precision = self::FLOAT_PRECISION,
        string $roundingMode = self::ROUNDING
    ): float {
        $firstNumber = self::getNumber($target);
        $secondNumber = self::getNumber($factor);

        $result = $firstNumber->times($secondNumber);

        return (float) $result->toPrecision($precision, $roundingMode);
    }

    public static function divide(
        float $target,
        float $divisor,
        int $precision = self::FLOAT_PRECISION,
        string $roundingMode = self::ROUNDING
    ): float {
        $firstNumber = self::getNumber($target);
        $secondNumber = self::getNumber($divisor);

        $result = $firstNumber->dividedBy($secondNumber, $precision);

        return (float) $result->toPrecision($precision, $roundingMode);
    }

    public static function isEqual($a, $b)
    {
        $firstNumber = self::getNumber($a);
        $secondNumber = self::getNumber($b);

        return $firstNumber->equals($secondNumber);
    }

    public static function isLowerThan($a, $b)
    {
        $firstNumber = self::getNumber($a);
        $secondNumber = self::getNumber($b);

        return $firstNumber->isLowerThan($secondNumber);
    }

    public static function isLowerOrEqualThan($a, $b)
    {
        $firstNumber = self::getNumber($a);
        $secondNumber = self::getNumber($b);

        return $firstNumber->isLowerOrEqualThan($secondNumber);
    }

    public static function isGreaterThan(float $target, float $comparison): bool
    {
        $firstNumber = self::getNumber($target);
        $secondNumber = self::getNumber($comparison);

        return $firstNumber->isGreaterThan($secondNumber);
    }

    public static function minus($a, $b)
    {
        $firstNumber = self::getNumber($a);
        $secondNumber = self::getNumber($b);

        return (float) ((string) $firstNumber->minus($secondNumber));
    }

    public static function plus($a, $b)
    {
        $firstNumber = self::getNumber($a);
        $secondNumber = self::getNumber($b);

        return (float) ((string) $firstNumber->plus($secondNumber));
    }

    /**
     * @param float $number
     *
     * @return Number|DecimalNumber
     */
    private static function getNumber(float $number)
    {
        if (is_subclass_of(Number::class, DecimalNumber::class)) {
            return new DecimalNumber((string) $number);
        }

        return new Number((string) $number);
    }
}
