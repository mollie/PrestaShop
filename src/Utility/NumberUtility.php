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
use PrestaShop\Decimal\Exception\DivisionByZeroException;
use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;

if (!defined('_PS_VERSION_')) {
    exit;
}

class NumberUtility
{
    public const DECIMAL_PRECISION = 2;
    public const FLOAT_PRECISION = 6;
    private const ROUNDING_MODE = Rounding::ROUND_HALF_UP;

    /**
     * Converts a float number to a specified precision.
     *
     * @param float $number
     * @param int $precision
     * @param string $roundingMode
     *
     * @return float
     */
    public static function toPrecision(
        float $number,
        int $precision = self::DECIMAL_PRECISION,
        string $roundingMode = self::ROUNDING_MODE
    ): float {
        $decimalNumber = self::getNumber($number);

        return (float) $decimalNumber->toPrecision($precision, $roundingMode);
    }

    /**
     * Decreases a number by a given percentage.
     *
     * @param float $number
     * @param float $percentage
     *
     * @return float
     *
     * @throws DivisionByZeroException
     */
    public static function decreaseByPercentage(float $number, float $percentage): float
    {
        if ($percentage <= 0) {
            return $number;
        }

        $numberTransformed = self::getNumber($number);
        $percentageIncrease = self::toPercentageIncrease($percentage);
        $decrement = $numberTransformed->dividedBy(self::getNumber($percentageIncrease));

        return (float) $decrement->toPrecision(self::DECIMAL_PRECISION, self::ROUNDING_MODE);
    }

    /**
     * Increases a number by a given percentage.
     *
     * @param float $number
     * @param float $percentage
     *
     * @return float
     */
    public static function increaseByPercentage(float $number, float $percentage): float
    {
        if ($percentage <= 0) {
            return $number;
        }

        $numberTransformed = self::getNumber($number);
        $percentageIncrease = self::toPercentageIncrease($percentage);
        $result = $numberTransformed->times(self::getNumber($percentageIncrease));

        return (float) $result->toPrecision(self::DECIMAL_PRECISION, self::ROUNDING_MODE);
    }

    /**
     * Converts a percentage to its decimal increase (e.g., 21% becomes 1.21).
     *
     * @param float $percentage
     *
     * @return float
     */
    public static function toPercentageIncrease(float $percentage): float
    {
        $percentageNumber = self::getNumber($percentage);
        $smallerNumber = $percentageNumber->dividedBy(self::getNumber(100));
        $result = $smallerNumber->plus(self::getNumber(1));

        return (float) $result->toPrecision(self::FLOAT_PRECISION, self::ROUNDING_MODE);
    }

    /**
     * Multiplies two numbers with precision.
     *
     * @param float $target
     * @param float $factor
     * @param int $precision
     * @param string $roundingMode
     *
     * @return float
     */
    public static function times(
        float $target,
        float $factor,
        int $precision = self::FLOAT_PRECISION,
        string $roundingMode = self::ROUNDING_MODE
    ): float {
        $result = self::getNumber($target)->times(self::getNumber($factor));

        return (float) $result->toPrecision($precision, $roundingMode);
    }

    /**
     * Divides a number by another with precision.
     *
     * @param float $target
     * @param float $divisor
     * @param int $precision
     * @param string $roundingMode
     *
     * @return float
     *
     * @throws DivisionByZeroException
     */
    public static function divide(
        float $target,
        float $divisor,
        int $precision = self::FLOAT_PRECISION,
        string $roundingMode = self::ROUNDING_MODE
    ): float {
        $result = self::getNumber($target)->dividedBy(self::getNumber($divisor), $precision);

        return (float) $result->toPrecision($precision, $roundingMode);
    }

    /**
     * Checks if two numbers are equal.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     */
    public static function isEqual(float $a, float $b): bool
    {
        return self::getNumber($a)->equals(self::getNumber($b));
    }

    /**
     * Checks if one number is lower than another.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     */
    public static function isLowerThan(float $a, float $b): bool
    {
        return self::getNumber($a)->isLowerThan(self::getNumber($b));
    }

    /**
     * Checks if one number is lower than or equal to another.
     *
     * @param float $a
     * @param float $b
     *
     * @return bool
     */
    public static function isLowerOrEqualThan(float $a, float $b): bool
    {
        return self::getNumber($a)->isLowerOrEqualThan(self::getNumber($b));
    }

    /**
     * Checks if one number is greater than another.
     *
     * @param float $target
     * @param float $comparison
     *
     * @return bool
     */
    public static function isGreaterThan(float $target, float $comparison): bool
    {
        return self::getNumber($target)->isGreaterThan(self::getNumber($comparison));
    }

    /**
     * Subtracts one number from another.
     *
     * @param float $a
     * @param float $b
     *
     * @return float
     */
    public static function minus(float $a, float $b): float
    {
        return (float) self::getNumber($a)->minus(self::getNumber($b))->toPrecision(self::FLOAT_PRECISION, self::ROUNDING_MODE);
    }

    /**
     * Adds two numbers together.
     *
     * @param float $a
     * @param float $b
     *
     * @return float
     */
    public static function plus(float $a, float $b): float
    {
        return (float) self::getNumber($a)->plus(self::getNumber($b))->toPrecision(self::FLOAT_PRECISION, self::ROUNDING_MODE);
    }

    /**
     * Creates a Number or DecimalNumber instance based on the current environment.
     *
     * @param float $number
     *
     * @return DecimalNumber|Number
     */
    private static function getNumber(float $number)
    {
        if (is_subclass_of(Number::class, DecimalNumber::class)) {
            return new DecimalNumber((string) $number);
        }

        return new Number((string) $number);
    }
}
