<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace MolliePrefix\PrestaShop\Decimal\Operation;

use MolliePrefix\PrestaShop\Decimal\DecimalNumber;
/**
 * Allows transforming a decimal number's precision
 */
class Rounding
{
    const ROUND_TRUNCATE = 'truncate';
    const ROUND_CEIL = 'ceil';
    const ROUND_FLOOR = 'floor';
    const ROUND_HALF_UP = 'up';
    const ROUND_HALF_DOWN = 'down';
    const ROUND_HALF_EVEN = 'even';
    /**
     * Rounds a decimal number to a specified precision
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     * @param string $roundingMode Rounding algorithm
     *
     * @return DecimalNumber
     */
    public function compute(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision, $roundingMode)
    {
        switch ($roundingMode) {
            case self::ROUND_HALF_UP:
                return $this->roundHalfUp($number, $precision);
                break;
            case self::ROUND_CEIL:
                return $this->ceil($number, $precision);
                break;
            case self::ROUND_FLOOR:
                return $this->floor($number, $precision);
                break;
            case self::ROUND_HALF_DOWN:
                return $this->roundHalfDown($number, $precision);
                break;
            case self::ROUND_TRUNCATE:
                return $this->truncate($number, $precision);
                break;
            case self::ROUND_HALF_EVEN:
                return $this->roundHalfEven($number, $precision);
                break;
        }
        throw new \InvalidArgumentException(\sprintf("Invalid rounding mode: %s", \print_r($roundingMode, \true)));
    }
    /**
     * Truncates a number to a target number of decimal digits.
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     *
     * @return DecimalNumber
     */
    public function truncate(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision)
    {
        $precision = $this->sanitizePrecision($precision);
        if ($number->getPrecision() <= $precision) {
            return $number;
        }
        if (0 === $precision) {
            return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number->getSign() . $number->getIntegerPart());
        }
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number->getSign() . $number->getIntegerPart() . '.' . \substr($number->getFractionalPart(), 0, $precision));
    }
    /**
     * Rounds a number up if its precision is greater than the target one.
     *
     * Ceil always rounds towards positive infinity.
     *
     * Examples:
     *
     * ```
     * $n = new Decimal\Number('123.456');
     * $this->ceil($n, 0); // '124'
     * $this->ceil($n, 1); // '123.5'
     * $this->ceil($n, 2); // '123.46'
     *
     * $n = new Decimal\Number('-123.456');
     * $this->ceil($n, 0); // '-122'
     * $this->ceil($n, 1); // '-123.3'
     * $this->ceil($n, 2); // '-123.44'
     * ```
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     *
     * @return DecimalNumber
     */
    public function ceil(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision)
    {
        $precision = $this->sanitizePrecision($precision);
        if ($number->getPrecision() <= $precision) {
            return $number;
        }
        if ($number->isNegative()) {
            // ceil works exactly as truncate for negative numbers
            return $this->truncate($number, $precision);
        }
        /**
         * The principle for ceil is the following:
         *
         * let X = number to round
         *     P = number of decimal digits that we want
         *     D = digit from the fractional part at index P
         *
         * if D > 0, ceil(X, P) = truncate(X + 10^(-P), P)
         * if D = 0, ceil(X, P) = truncate(X, P)
         */
        if ($precision > 0) {
            // we know that D > 0, because we have already checked that the number's precision
            // is greater than the target precision
            $numberToAdd = '0.' . \str_pad('1', $precision, '0', \STR_PAD_LEFT);
        } else {
            $numberToAdd = '1';
        }
        return $this->truncate($number, $precision)->plus(new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($numberToAdd));
    }
    /**
     * Rounds a number down if its precision is greater than the target one.
     *
     * Floor always rounds towards negative infinity.
     *
     * Examples:
     *
     * ```
     * $n = new Decimal\Number('123.456');
     * $this->floor($n, 0); // '123'
     * $this->floor($n, 1); // '123.4'
     * $this->floor($n, 2); // '123.45'
     *
     * $n = new Decimal\Number('-123.456');
     * $this->floor($n, 0); // '-124'
     * $this->floor($n, 1); // '-123.5'
     * $this->floor($n, 2); // '-123.46'
     * ```
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     *
     * @return DecimalNumber
     */
    public function floor(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision)
    {
        $precision = $this->sanitizePrecision($precision);
        if ($number->getPrecision() <= $precision) {
            return $number;
        }
        if ($number->isPositive()) {
            // floor works exactly as truncate for positive numbers
            return $this->truncate($number, $precision);
        }
        /**
         * The principle for ceil is the following:
         *
         * let X = number to round
         *     P = number of decimal digits that we want
         *     D = digit from the fractional part at index P
         *
         * if D < 0, ceil(X, P) = truncate(X - 10^(-P), P)
         * if D = 0, ceil(X, P) = truncate(X, P)
         */
        if ($precision > 0) {
            // we know that D > 0, because we have already checked that the number's precision
            // is greater than the target precision
            $numberToSubtract = '0.' . \str_pad('1', $precision, '0', \STR_PAD_LEFT);
        } else {
            $numberToSubtract = '1';
        }
        return $this->truncate($number, $precision)->minus(new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($numberToSubtract));
    }
    /**
     * Rounds the number according to the digit D located at precision P.
     * - It rounds away from zero if D >= 5
     * - It rounds towards zero if D < 5
     *
     * Examples:
     *
     * ```
     * $n = new Decimal\Number('123.456');
     * $this->roundHalfUp($n, 0); // '123'
     * $this->roundHalfUp($n, 1); // '123.5'
     * $this->roundHalfUp($n, 2); // '123.46'
     *
     * $n = new Decimal\Number('-123.456');
     * $this->roundHalfUp($n, 0); // '-123'
     * $this->roundHalfUp($n, 1); // '-123.5'
     * $this->roundHalfUp($n, 2); // '-123.46'
     * ```
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     *
     * @return DecimalNumber
     */
    public function roundHalfUp(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision)
    {
        return $this->roundHalf($number, $precision, 5);
    }
    /**
     * Rounds the number according to the digit D located at precision P.
     * - It rounds away from zero if D > 5
     * - It rounds towards zero if D <= 5
     *
     * Examples:
     *
     * ```
     * $n = new Decimal\Number('123.456');
     * $this->roundHalfUp($n, 0); // '123'
     * $this->roundHalfUp($n, 1); // '123.4'
     * $this->roundHalfUp($n, 2); // '123.46'
     *
     * $n = new Decimal\Number('-123.456');
     * $this->roundHalfUp($n, 0); // '-123'
     * $this->roundHalfUp($n, 1); // '-123.4'
     * $this->roundHalfUp($n, 2); // '-123.46'
     * ```
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     *
     * @return DecimalNumber
     */
    public function roundHalfDown(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision)
    {
        return $this->roundHalf($number, $precision, 6);
    }
    /**
     * Rounds a number according to "banker's rounding".
     *
     * The number is rounded according to the digit D located at precision P.
     * - Away from zero if D > 5
     * - Towards zero if D < 5
     * - if D = 5, then
     *     - If the last significant digit is even, the number is rounded away from zero
     *     - If the last significant digit is odd, the number is rounded towards zero.
     *
     * Examples:
     *
     * ```
     * $n = new Decimal\Number('123.456');
     * $this->roundHalfUp($n, 0); // '123'
     * $this->roundHalfUp($n, 1); // '123.4'
     * $this->roundHalfUp($n, 2); // '123.46'
     *
     * $n = new Decimal\Number('-123.456');
     * $this->roundHalfUp($n, 0); // '-123'
     * $this->roundHalfUp($n, 1); // '-123.4'
     * $this->roundHalfUp($n, 2); // '-123.46'
     *
     * $n = new Decimal\Number('1.1525354556575859505');
     * $this->roundHalfEven($n, 0);  // '1'
     * $this->roundHalfEven($n, 1);  // '1.2'
     * $this->roundHalfEven($n, 2);  // '1.15'
     * $this->roundHalfEven($n, 3);  // '1.152'
     * $this->roundHalfEven($n, 4);  // '1.1525'
     * $this->roundHalfEven($n, 5);  // '1.15255'
     * $this->roundHalfEven($n, 6);  // '1.152535'
     * $this->roundHalfEven($n, 7);  // '1.1525354'
     * $this->roundHalfEven($n, 8);  // '1.15253546'
     * $this->roundHalfEven($n, 9);  // '1.152535456'
     * $this->roundHalfEven($n, 10); // '1.1525354556'
     * ```
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     *
     * @return DecimalNumber
     */
    public function roundHalfEven(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision)
    {
        $precision = $this->sanitizePrecision($precision);
        if ($number->getPrecision() <= $precision) {
            return $number;
        }
        /**
         * The principle for roundHalfEven is the following:
         *
         * let X = number to round
         *     P = number of decimal digits that we want
         *     D = digit from the fractional part at index P
         *     E = digit to the left of D
         *
         * if D != 5, roundHalfEven(X, P) = roundHalfUp(X, P)
         * if D = 5 and E is even, roundHalfEven(X, P) = truncate(X, P)
         * if D = 5 and E is odd and X is positive, roundHalfUp(X, P) = ceil(X, P)
         * if D = 5 and E is odd and X is negative, roundHalfUp(X, P) = floor(X, P)
         */
        $fractionalPart = $number->getFractionalPart();
        $digit = (int) $fractionalPart[$precision];
        if ($digit !== 5) {
            return $this->roundHalfUp($number, $precision);
        }
        // retrieve the digit to the left of it
        if ($precision === 0) {
            $referenceDigit = (int) \substr($number->getIntegerPart(), -1);
        } else {
            $referenceDigit = (int) $fractionalPart[$precision - 1];
        }
        // truncate if even
        $isEven = $referenceDigit % 2 === 0;
        if ($isEven) {
            return $this->truncate($number, $precision);
        }
        // round away from zero
        $method = $number->isPositive() ? self::ROUND_CEIL : self::ROUND_FLOOR;
        return $this->compute($number, $precision, $method);
    }
    /**
     * Rounds the number according to the digit D located at precision P.
     * - It rounds away from zero if D >= $halfwayValue
     * - It rounds towards zero if D < $halfWayValue
     *
     * @param DecimalNumber $number Number to round
     * @param int $precision Maximum number of decimals
     * @param int $halfwayValue Threshold upon which the rounding will be performed
     *  away from zero instead of towards zero.
     *
     * @return DecimalNumber
     */
    private function roundHalf(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $precision, $halfwayValue)
    {
        $precision = $this->sanitizePrecision($precision);
        if ($number->getPrecision() <= $precision) {
            return $number;
        }
        /**
         * The principle for roundHalf is the following:
         *
         * let X = number to round
         *     P = number of decimal digits that we want
         *     D = digit from the fractional part at index P
         *     Y = digit considered as the half-way value on which we round up (usually 5 or 6)
         *
         * if D >= Y, roundHalf(X, P) = ceil(X, P)
         * if D < Y, roundHalf(X, P) = truncate(X, P)
         */
        $fractionalPart = $number->getFractionalPart();
        $digit = (int) $fractionalPart[$precision];
        if ($digit >= $halfwayValue) {
            // round away from zero
            $mode = $number->isPositive() ? self::ROUND_CEIL : self::ROUND_FLOOR;
            return $this->compute($number, $precision, $mode);
        }
        // round towards zero
        return $this->truncate($number, $precision);
    }
    /**
     * Ensures that precision is a positive int
     *
     * @param mixed $precision
     *
     * @return int Precision
     *
     * @throws \InvalidArgumentException if precision is not a positive integer
     */
    private function sanitizePrecision($precision)
    {
        if (!\is_numeric($precision) || $precision < 0) {
            throw new \InvalidArgumentException(\sprintf('Invalid precision: %s', \print_r($precision, \true)));
        }
        return (int) $precision;
    }
}
