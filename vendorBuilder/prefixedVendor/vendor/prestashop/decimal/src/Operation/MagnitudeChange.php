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
 * Computes relative magnitude changes on a decimal number
 */
class MagnitudeChange
{
    /**
     * Multiplies a number by 10^$exponent.
     *
     * Examples:
     * ```php
     * $n = new Decimal\Number('123.45678');
     * $o = new Decimal\Operation\MagnitudeChange();
     * $o->compute($n, 2);  // 12345.678
     * $o->compute($n, 6);  // 123456780
     * $o->compute($n, -2); // 1.2345678
     * $o->compute($n, -6); // 0.00012345678
     * ```
     *
     * @param DecimalNumber $number
     * @param int $exponent
     *
     * @return DecimalNumber
     */
    public function compute(\MolliePrefix\PrestaShop\Decimal\DecimalNumber $number, $exponent)
    {
        $exponent = (int) $exponent;
        if ($exponent === 0) {
            return $number;
        }
        $resultingExponent = $exponent - $number->getExponent();
        if ($resultingExponent <= 0) {
            return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number->getSign() . $number->getCoefficient(), \abs($resultingExponent));
        }
        // add zeroes
        $targetLength = \strlen($number->getCoefficient()) + $resultingExponent;
        return new \MolliePrefix\PrestaShop\Decimal\DecimalNumber($number->getSign() . \str_pad($number->getCoefficient(), $targetLength, '0'));
    }
}
