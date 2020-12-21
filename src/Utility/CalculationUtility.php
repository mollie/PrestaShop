<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

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
}
