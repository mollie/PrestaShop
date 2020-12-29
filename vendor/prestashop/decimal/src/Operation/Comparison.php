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
 * Compares two decimal numbers
 */
class Comparison
{
    /**
     * Compares two decimal numbers.
     *
     * @param DecimalNumber $a
     * @param DecimalNumber $b
     *
     * @return int Returns 1 if $a > $b, -1 if $a < $b, and 0 if they are equal.
     */
    public function compare(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        if (\function_exists('MolliePrefix\\bccomp')) {
            return $this->compareUsingBcMath($a, $b);
        }
        return $this->compareWithoutBcMath($a, $b);
    }
    /**
     * Compares two decimal numbers using BC Math
     *
     * @param DecimalNumber $a
     * @param DecimalNumber $b
     *
     * @return int Returns 1 if $a > $b, -1 if $a < $b, and 0 if they are equal.
     */
    public function compareUsingBcMath(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        return bccomp((string) $a, (string) $b, \max($a->getExponent(), $b->getExponent()));
    }
    /**
     * Compares two decimal numbers without using BC Math
     *
     * @param DecimalNumber $a
     * @param DecimalNumber $b
     *
     * @return int Returns 1 if $a > $b, -1 if $a < $b, and 0 if they are equal.
     */
    public function compareWithoutBcMath(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        $signCompare = $this->compareSigns($a->getSign(), $b->getSign());
        if ($signCompare !== 0) {
            return $signCompare;
        }
        // signs are equal, compare regardless of sign
        $result = $this->positiveCompare($a, $b);
        // inverse the result if the signs are negative
        if ($a->isNegative()) {
            return -$result;
        }
        return $result;
    }
    /**
     * Compares two decimal numbers as positive regardless of sign.
     *
     * @param DecimalNumber $a
     * @param DecimalNumber $b
     *
     * @return int Returns 1 if $a > $b, -1 if $a < $b, and 0 if they are equal.
     */
    private function positiveCompare(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $a, \MolliePrefix\PrestaShop\Decimal\DecimalNumber $b)
    {
        // compare integer length
        $intLengthCompare = $this->compareNumeric(\strlen($a->getIntegerPart()), \strlen($b->getIntegerPart()));
        if ($intLengthCompare !== 0) {
            return $intLengthCompare;
        }
        // integer parts are equal in length, compare integer part
        $intPartCompare = $this->compareBinary($a->getIntegerPart(), $b->getIntegerPart());
        if ($intPartCompare !== 0) {
            return $intPartCompare;
        }
        // integer parts are equal, compare fractional part
        return $this->compareBinary($a->getFractionalPart(), $b->getFractionalPart());
    }
    /**
     * Compares positive/negative signs.
     *
     * @param string $a
     * @param string $b
     *
     * @return int Returns 0 if both signs are equal, 1 if $a is positive, and -1 if $b is positive
     */
    private function compareSigns($a, $b)
    {
        if ($a === $b) {
            return 0;
        }
        // empty string means positive sign
        if ($a === '') {
            return 1;
        }
        return -1;
    }
    /**
     * Compares two values numerically.
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return int Returns 1 if $a > $b, -1 if $a < $b, and 0 if they are equal.
     */
    private function compareNumeric($a, $b)
    {
        if ($a < $b) {
            return -1;
        }
        if ($a > $b) {
            return 1;
        }
        return 0;
    }
    /**
     * Compares two strings binarily.
     *
     * @param string $a
     * @param string $b
     *
     * @return int Returns 1 if $a > $b, -1 if $a < $b, and 0 if they are equal.
     */
    private function compareBinary($a, $b)
    {
        $comparison = \strcmp($a, $b);
        if ($comparison > 0) {
            return 1;
        }
        if ($comparison < 0) {
            return -1;
        }
        return 0;
    }
}
