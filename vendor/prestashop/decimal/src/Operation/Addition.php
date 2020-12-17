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
 * Computes the addition of two decimal numbers
 */
class Addition
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
     * Performs the addition
     *
     * @param DecimalNumber $a Base number
     * @param DecimalNumber $b Addend
     *
     * @return DecimalNumber Result of the addition
     */
    public function compute(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        if (\function_exists('MolliePrefix\\bcadd')) {
            return $this->computeUsingBcMath($a, $b);
        }
        return $this->computeWithoutBcMath($a, $b);
    }
    /**
     * Performs the addition using BC Math
     *
     * @param DecimalNumber $a Base number
     * @param DecimalNumber $b Addend
     *
     * @return DecimalNumber Result of the addition
     */
    public function computeUsingBcMath(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        $precision1 = $a->getPrecision();
        $precision2 = $b->getPrecision();
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber((string) bcadd($a, $b, \max($precision1, $precision2)));
    }
    /**
     * Performs the addition without BC Math
     *
     * @param DecimalNumber $a Base number
     * @param DecimalNumber $b Addend
     *
     * @return DecimalNumber Result of the addition
     */
    public function computeWithoutBcMath(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        if ($a->isNegative()) {
            if ($b->isNegative()) {
                // if both numbers are negative,
                // we can just add them as positive numbers and then invert the sign
                // f(x, y) = -(|x| + |y|)
                // eg. f(-1, -2) = -(|-1| + |-2|) = -3
                // eg. f(-2, -1) = -(|-2| + |-1|) = -3
                return $this->computeWithoutBcMath($a->toPositive(), $b->toPositive())->invert();
            }
            // if the number is negative and the addend positive,
            // perform an inverse subtraction by inverting the terms
            // f(x, y) = y - |x|
            // eg. f(-2, 1) = 1 - |-2| = -1
            // eg. f(-1, 2) = 2 - |-1| = 1
            // eg. f(-1, 1) = 1 - |-1| = 0
            return $b->minus($a->toPositive());
        }
        if ($b->isNegative()) {
            // if the number is positive and the addend is negative
            // perform subtraction instead: 2 - 1
            // f(x, y) = x - |y|
            // f(2, -1) = 2 - |-1| = 1
            // f(1, -2) = 1 - |-2| = -1
            // f(1, -1) = 1 - |-1| = 0
            return $a->minus($b->toPositive());
        }
        // optimization: 0 + x = x
        if ('0' === (string) $a) {
            return $b;
        }
        // optimization: x + 0 = x
        if ('0' === (string) $b) {
            return $a;
        }
        // pad coefficients with leading/trailing zeroes
        list($coeff1, $coeff2) = $this->normalizeCoefficients($a, $b);
        // compute the coefficient sum
        $sum = $this->addStrings($coeff1, $coeff2);
        // both signs are equal, so we can use either
        $sign = $a->getSign();
        // keep the bigger exponent
        $exponent = \max($a->getExponent(), $b->getExponent());
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($sign . $sum, $exponent);
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
     * Adds two integer numbers as strings.
     *
     * @param string $number1
     * @param string $number2
     * @param bool $fractional [default=false]
     * If true, the numbers will be treated as the fractional part of a number (padded with trailing zeroes).
     * Otherwise, they will be treated as the integer part (padded with leading zeroes).
     *
     * @return string
     */
    private function addStrings($number1, $number2, $fractional = \false)
    {
        // optimization - numbers can be treated as integers as long as they don't overflow the max int size
        if ('0' !== $number1[0] && '0' !== $number2[0] && \strlen($number1) <= $this->maxSafeIntStringSize && \strlen($number2) <= $this->maxSafeIntStringSize) {
            return (string) ((int) $number1 + (int) $number2);
        }
        // find out which of the strings is longest
        $maxLength = \max(\strlen($number1), \strlen($number2));
        // add leading or trailing zeroes as needed
        $number1 = \str_pad($number1, $maxLength, '0', $fractional ? \STR_PAD_RIGHT : \STR_PAD_LEFT);
        $number2 = \str_pad($number2, $maxLength, '0', $fractional ? \STR_PAD_RIGHT : \STR_PAD_LEFT);
        $result = '';
        $carryOver = 0;
        for ($i = $maxLength - 1; 0 <= $i; $i--) {
            $sum = $number1[$i] + $number2[$i] + $carryOver;
            $result .= $sum % 10;
            $carryOver = (int) ($sum >= 10);
        }
        if ($carryOver > 0) {
            $result .= '1';
        }
        return \strrev($result);
    }
}
