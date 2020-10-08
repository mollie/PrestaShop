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
 * Computes the subtraction of two decimal numbers
 */
class Subtraction
{
    /**
     * Maximum safe string size in order to be confident
     * that it won't overflow the max int size when operating with it
     * @var int
     */
    private $maxSafeIntStringSize;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->maxSafeIntStringSize = \strlen((string) \PHP_INT_MAX) - 1;
    }
    /**
     * Performs the subtraction
     *
     * @param DecimalNumber $a Minuend
     * @param DecimalNumber $b Subtrahend
     *
     * @return DecimalNumber Result of the subtraction
     */
    public function compute(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        if (\function_exists('MolliePrefix\\bcsub')) {
            return $this->computeUsingBcMath($a, $b);
        }
        return $this->computeWithoutBcMath($a, $b);
    }
    /**
     * Performs the subtraction using BC Math
     *
     * @param DecimalNumber $a Minuend
     * @param DecimalNumber $b Subtrahend
     *
     * @return DecimalNumber Result of the subtraction
     */
    public function computeUsingBcMath(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        $precision1 = $a->getPrecision();
        $precision2 = $b->getPrecision();
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber((string) bcsub($a, $b, \max($precision1, $precision2)));
    }
    /**
     * Performs the subtraction without using BC Math
     *
     * @param DecimalNumber $a Minuend
     * @param DecimalNumber $b Subtrahend
     *
     * @return DecimalNumber Result of the subtraction
     */
    public function computeWithoutBcMath(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        if ($a->isNegative()) {
            if ($b->isNegative()) {
                // if both minuend and subtrahend are negative
                // perform the subtraction with inverted coefficients position and sign
                // f(x, y) = |y| - |x|
                // eg. f(-1, -2) = |-2| - |-1| = 2 - 1 = 1
                // e.g. f(-2, -1) =  |-1| - |-2| = 1 - 2 = -1
                return $this->computeWithoutBcMath($b->toPositive(), $a->toPositive());
            } else {
                // if the minuend is negative and the subtrahend is positive,
                // we can just add them as positive numbers and then invert the sign
                // f(x, y) = -(|x| + y)
                // eg. f(1, 2) = -(|-1| + 2) = -3
                // eg. f(-2, 1) = -(|-2| + 1) = -3
                return $a->toPositive()->plus($b)->toNegative();
            }
        } else {
            if ($b->isNegative()) {
                // if the minuend is positive subtrahend is negative, perform an addition
                // f(x, y) = x + |y|
                // eg. f(2, -1) = 2 + |-1| = 2 + 1 = 3
                return $a->plus($b->toPositive());
            }
        }
        // optimization: 0 - x = -x
        if ('0' === (string) $a) {
            return !$b->isNegative() ? $b->toNegative() : $b;
        }
        // optimization: x - 0 = x
        if ('0' === (string) $b) {
            return $a;
        }
        // pad coefficients with leading/trailing zeroes
        list($coeff1, $coeff2) = $this->normalizeCoefficients($a, $b);
        // compute the coefficient subtraction
        if ($a->isGreaterThan($b)) {
            $sub = $this->subtractStrings($coeff1, $coeff2);
            $sign = '';
        } else {
            $sub = $this->subtractStrings($coeff2, $coeff1);
            $sign = '-';
        }
        // keep the bigger exponent
        $exponent = \max($a->getExponent(), $b->getExponent());
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($sign . $sub, $exponent);
    }
    /**
     * Normalizes coefficients by adding leading or trailing zeroes as needed so that both are the same length
     *
     * @param DecimalNumber $a
     * @param DecimalNumber $b
     *
     * @return array An array containing the normalized coefficients
     */
    private function normalizeCoefficients(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        $exp1 = $a->getExponent();
        $exp2 = $b->getExponent();
        $coeff1 = $a->getCoefficient();
        $coeff2 = $b->getCoefficient();
        // add trailing zeroes if needed
        if ($exp1 > $exp2) {
            $coeff2 = \str_pad($coeff2, \strlen($coeff2) + $exp1 - $exp2, '0', \STR_PAD_RIGHT);
        } elseif ($exp1 < $exp2) {
            $coeff1 = \str_pad($coeff1, \strlen($coeff1) + $exp2 - $exp1, '0', \STR_PAD_RIGHT);
        }
        $len1 = \strlen($coeff1);
        $len2 = \strlen($coeff2);
        // add leading zeroes if needed
        if ($len1 > $len2) {
            $coeff2 = \str_pad($coeff2, $len1, '0', \STR_PAD_LEFT);
        } elseif ($len1 < $len2) {
            $coeff1 = \str_pad($coeff1, $len2, '0', \STR_PAD_LEFT);
        }
        return [$coeff1, $coeff2];
    }
    /**
     * Subtracts $number2 to $number1.
     * For this algorithm to work, $number1 has to be >= $number 2.
     *
     * @param string $number1
     * @param string $number2
     * @param bool $fractional [default=false]
     * If true, the numbers will be treated as the fractional part of a number (padded with trailing zeroes).
     * Otherwise, they will be treated as the integer part (padded with leading zeroes).
     *
     * @return string
     */
    private function subtractStrings($number1, $number2, $fractional = \false)
    {
        // find out which of the strings is longest
        $maxLength = \max(\strlen($number1), \strlen($number2));
        // add leading or trailing zeroes as needed
        $number1 = \str_pad($number1, $maxLength, '0', $fractional ? \STR_PAD_RIGHT : \STR_PAD_LEFT);
        $number2 = \str_pad($number2, $maxLength, '0', $fractional ? \STR_PAD_RIGHT : \STR_PAD_LEFT);
        $result = '';
        $carryOver = 0;
        for ($i = $maxLength - 1; 0 <= $i; $i--) {
            $operand1 = $number1[$i] - $carryOver;
            $operand2 = $number2[$i];
            if ($operand1 >= $operand2) {
                $result .= $operand1 - $operand2;
                $carryOver = 0;
            } else {
                $result .= 10 + $operand1 - $operand2;
                $carryOver = 1;
            }
        }
        return \strrev($result);
    }
}
