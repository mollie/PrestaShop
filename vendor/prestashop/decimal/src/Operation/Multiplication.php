<?php

/**
 * This file is part of the PrestaShop\Decimal package
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @license   https://opensource.org/licenses/MIT MIT License
 */
namespace _PhpScoper5ea00cc67502b\PrestaShop\Decimal\Operation;

use _PhpScoper5ea00cc67502b\PrestaShop\Decimal\Number as DecimalNumber;
use function floor;
use function function_exists;
use function ltrim;
use function str_pad;
use function strlen;
use function strrev;

/**
 * Computes the multiplication between two decimal numbers
 */
class Multiplication
{
    /**
     * Performs the multiplication
     *
     * @param DecimalNumber $a Left operand
     * @param DecimalNumber $b Right operand
     *
     * @return DecimalNumber Result of the multiplication
     */
    public function compute(DecimalNumber $a, DecimalNumber $b)
    {
        if (function_exists('_PhpScoper5ea00cc67502b\\bcmul')) {
            return $this->computeUsingBcMath($a, $b);
        }
        return $this->computeWithoutBcMath($a, $b);
    }
    /**
     * Performs the multiplication using BC Math
     *
     * @param DecimalNumber $a Left operand
     * @param DecimalNumber $b Right operand
     *
     * @return DecimalNumber Result of the multiplication
     */
    public function computeUsingBcMath(DecimalNumber $a, DecimalNumber $b)
    {
        $precision1 = $a->getPrecision();
        $precision2 = $b->getPrecision();
        return new DecimalNumber((string) bcmul($a, $b, $precision1 + $precision2));
    }
    /**
     * Performs the multiplication without BC Math
     *
     * @param DecimalNumber $a Left operand
     * @param DecimalNumber $b Right operand
     *
     * @return DecimalNumber Result of the multiplication
     */
    public function computeWithoutBcMath(DecimalNumber $a, DecimalNumber $b)
    {
        $aAsString = (string) $a;
        $bAsString = (string) $b;
        // optimization: if either one is zero, the result is zero
        if ('0' === $aAsString || '0' === $bAsString) {
            return new DecimalNumber('0');
        }
        // optimization: if either one is one, the result is the other one
        if ('1' === $aAsString) {
            return $b;
        }
        if ('1' === $bAsString) {
            return $a;
        }
        $result = $this->multiplyStrings(ltrim($a->getCoefficient(), '0'), ltrim($b->getCoefficient(), '0'));
        $sign = ($a->isNegative() xor $b->isNegative()) ? '-' : '';
        // a multiplication has at most as many decimal figures as the sum
        // of the number of decimal figures the factors have
        $exponent = $a->getExponent() + $b->getExponent();
        return new DecimalNumber($sign . $result, $exponent);
    }
    /**
     * Multiplies two integer numbers as strings.
     *
     * This method implements a naive "long multiplication" algorithm.
     *
     * @param string $topNumber
     * @param string $bottomNumber
     *
     * @return string
     */
    private function multiplyStrings($topNumber, $bottomNumber)
    {
        $topNumberLength = strlen($topNumber);
        $bottomNumberLength = strlen($bottomNumber);
        if ($topNumberLength < $bottomNumberLength) {
            // multiplication is commutative, and this algorithm
            // performs better if the bottom number is shorter.
            return $this->multiplyStrings($bottomNumber, $topNumber);
        }
        $stepNumber = 0;
        $result = new DecimalNumber('0');
        for ($i = $bottomNumberLength - 1; $i >= 0; $i--) {
            $carryOver = 0;
            $partialResult = '';
            // optimization: we don't need to bother multiplying by zero
            if ($bottomNumber[$i] === '0') {
                $stepNumber++;
                continue;
            }
            if ($bottomNumber[$i] === '1') {
                // multiplying by one is the same as copying the top number
                $partialResult = strrev($topNumber);
            } else {
                // digit-by-digit multiplication using carry-over
                for ($j = $topNumberLength - 1; $j >= 0; $j--) {
                    $multiplicationResult = $bottomNumber[$i] * $topNumber[$j] + $carryOver;
                    $carryOver = floor($multiplicationResult / 10);
                    $partialResult .= $multiplicationResult % 10;
                }
                if ($carryOver > 0) {
                    $partialResult .= $carryOver;
                }
            }
            // pad the partial result with as many zeros as performed steps
            $padding = str_pad('', $stepNumber, '0');
            $partialResult = $padding . $partialResult;
            // add to the result
            $result = $result->plus(new DecimalNumber(strrev($partialResult)));
            $stepNumber++;
        }
        return (string) $result;
    }
}
