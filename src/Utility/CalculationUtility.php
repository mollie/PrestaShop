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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CalculationUtility
{
    /**
     * @param float $productPrice
     * @param float $shippingPrice
     * @param float $wrappingPrice
     *
     * @return float
     */
    public static function getCartRemainingPrice($productPrice, $shippingPrice, $wrappingPrice)
    {
        return
            NumberUtility::minus(
                NumberUtility::minus($productPrice, $shippingPrice),
                $wrappingPrice
            );
    }

    /**
     * @param float $unitPrice
     * @param float $targetVat
     *
     * @return float
     */
    public static function getUnitPriceNoTax($unitPrice, $targetVat)
    {
        return NumberUtility::divide(
            $unitPrice,
            NumberUtility::plus(
                1,
                NumberUtility::divide(
                    $targetVat,
                    100
                )
            )
        );
    }

    public static function getActualVatRate($unitPrice, $unitPriceNoTax, $quantity = 1)
    {
        $totalPrice = NumberUtility::times($unitPrice, $quantity);
        $totalPriceNoTax = NumberUtility::times($unitPriceNoTax, $quantity);
        $vatPrice = NumberUtility::minus($totalPrice, $totalPriceNoTax);
        $vatPriceDividedByTotalPriceNoTax = NumberUtility::divide($vatPrice, $totalPriceNoTax);

        return NumberUtility::times($vatPriceDividedByTotalPriceNoTax, 100);
    }

    /**
     * Calculates the VAT amount contained in a tax-inclusive total for a given VAT rate.
     *
     * @param float $totalAmount tax-inclusive total
     * @param float $vatRate VAT rate as a percentage (e.g. 19 for 19%)
     * @param int $precision
     *
     * @return float
     */
    public static function getVatAmount($totalAmount, $vatRate, $precision = NumberUtility::DECIMAL_PRECISION)
    {
        if ($vatRate <= 0) {
            return 0.0;
        }

        return round($totalAmount * $vatRate / ($vatRate + 100), $precision);
    }
}
