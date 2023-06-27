<?php

namespace Mollie\Calculator;

use Mollie\DTO\PaymentFeeData;
use Mollie\Utility\NumberUtility;
use TaxCalculator;

class PaymentFeeCalculator
{
    /** @var TaxCalculator */
    private $taxCalculator;

    public function __construct(TaxCalculator $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    public function calculateFixedFee(float $totalFeePriceTaxExcl): PaymentFeeData
    {
        $totalFeePriceTaxIncl = $this->taxCalculator->addTaxes($totalFeePriceTaxExcl);

        return $this->returnFormattedResult(
            $totalFeePriceTaxIncl,
            $totalFeePriceTaxExcl
        );
    }

    public function calculatePercentageFee(
        float $totalCartPriceTaxExcl,
        float $surchargePercentage,
        float $maxPercentage,
        float $surchargeLimit
    ): PaymentFeeData {
        $totalFeePriceTaxIncl = NumberUtility::times(
            $totalCartPriceTaxExcl,
            NumberUtility::divide($surchargePercentage, $maxPercentage)
        );

        if ($this->isPaymentFeeGreaterThanMaxLimit(
            $totalFeePriceTaxIncl,
            $surchargeLimit
        )) {
            return $this->calculateSurchargeMaxValue($surchargeLimit);
        }

        $totalFeePriceTaxExcl = $this->taxCalculator->removeTaxes($totalFeePriceTaxIncl);

        return $this->returnFormattedResult(
            $totalFeePriceTaxIncl,
            $totalFeePriceTaxExcl
        );
    }

    public function calculatePercentageAndFixedPriceFee(
        float $totalCartPriceTaxExcl,
        float $surchargePercentage,
        float $maxPercentage,
        float $surchargeFixedPriceTaxExcl,
        float $surchargeLimit
    ): PaymentFeeData {
        $surchargeFixedPriceTaxIncl = $this->taxCalculator->addTaxes($surchargeFixedPriceTaxExcl);

        $totalFeePriceTaxIncl = NumberUtility::plus(NumberUtility::times(
            $totalCartPriceTaxExcl,
            NumberUtility::divide($surchargePercentage, $maxPercentage)
        ), $surchargeFixedPriceTaxIncl);

        if ($this->isPaymentFeeGreaterThanMaxLimit(
            $totalFeePriceTaxIncl,
            $surchargeLimit
        )) {
            return $this->calculateSurchargeMaxValue($surchargeLimit);
        }

        $totalFeePriceTaxExcl = $this->taxCalculator->removeTaxes($totalFeePriceTaxIncl);

        return $this->returnFormattedResult(
            $totalFeePriceTaxIncl,
            $totalFeePriceTaxExcl
        );
    }

    public function calculateSurchargeMaxValue(float $surchargeMaxValue): PaymentFeeData
    {
        $totalFeePriceTaxIncl = $surchargeMaxValue;
        $totalFeePriceTaxExcl = $this->taxCalculator->removeTaxes($totalFeePriceTaxIncl);

        return $this->returnFormattedResult(
            $totalFeePriceTaxIncl,
            $totalFeePriceTaxExcl
        );
    }

    private function isPaymentFeeGreaterThanMaxLimit(
        float $totalFeePriceTaxIncl,
        float $surchargeLimit
    ): bool {
        if (NumberUtility::isGreaterThan($totalFeePriceTaxIncl, $surchargeLimit)) {
            return true;
        }

        return false;
    }

    private function returnFormattedResult(
        float $totalFeePriceTaxIncl,
        float $totalFeePriceTaxExcl
    ): PaymentFeeData {
        return new PaymentFeeData(
            $totalFeePriceTaxIncl,
            $totalFeePriceTaxExcl,
            $this->taxCalculator->getTotalRate(),
            $totalFeePriceTaxIncl > 0 && $totalFeePriceTaxExcl > 0
        );
    }
}
