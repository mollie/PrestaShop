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

use Mollie\Config\Config;
use MolPaymentMethod;
use PrestaShop\Decimal\Exception\DivisionByZeroException;
use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;

class PaymentFeeUtility
{
    private const MAX_PERCENTAGE = '100';
    private const LOWEST_VALUE = '0';
    private const CALCULATION_PRECISION = 6;
    private const FINAL_PRECISION = 2;

    /**
     * @throws DivisionByZeroException
     */
    public static function getPaymentFee(MolPaymentMethod $paymentMethod, string $totalCartPrice)
    {
//TODO REMOVE THIS
        $totalDecimalCartPrice = new Number($totalCartPrice);
        $maxPercentage = new Number(self::MAX_PERCENTAGE);
        $surchargePercentage = new Number($paymentMethod->surcharge_percentage);
        $surchargeFixedPrice = new Number($paymentMethod->surcharge_fixed_amount);

        switch ($paymentMethod->surcharge) {
            case Config::FEE_FIXED_FEE:
                $totalFeePrice = self::calculateTax($surchargeFixedPrice);

                break;
            case Config::FEE_PERCENTAGE:
                $totalFeePrice = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                );

                $totalFeePrice = self::calculateTax($totalFeePrice);

                break;
            case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
                $totalFeePrice = $totalDecimalCartPrice->times(
                    $surchargePercentage->dividedBy(
                        $maxPercentage
                    )
                )->plus($surchargeFixedPrice);

                $totalFeePrice = self::calculateTax($totalFeePrice);

                break;
            case Config::FEE_NO_FEE:
            default:
                return false;
        }

        $surchargeMaxValue = new Number($paymentMethod->surcharge_limit);
        $lowestValue = new Number(self::LOWEST_VALUE);

        if ($surchargeMaxValue->isGreaterThan($lowestValue) && $totalFeePrice->isGreaterOrEqualThan($surchargeMaxValue)) {
            $totalFeePrice = $surchargeMaxValue;
        }

        // TODO make method non static and get precision from context/configuration based on PS version.
        return $totalFeePrice->toPrecision(self::FINAL_PRECISION, Rounding::ROUND_HALF_UP);
    }

    private static function calculateTax(Number $totalFeePrice): Number
    {
        $tax = new \Tax();
        $tax->rate = 21;
        $tax_calculator = new \TaxCalculator(array($tax));
        return new Number((string) $tax_calculator->addTaxes($totalFeePrice->toPrecision(self::CALCULATION_PRECISION)));
    }
}
