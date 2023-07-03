<?php

namespace Mollie\Calculator;

use Mollie\Adapter\Context;
use Mollie\DTO\PaymentFeeData;
use Mollie\Utility\NumberUtility;
use TaxCalculator;

class PaymentFeeCalculator
{
    private const MAX_PERCENTAGE = 100;

    /** @var TaxCalculator */
    private $taxCalculator;
    /** @var Context */
    private $context;

    public function __construct(TaxCalculator $taxCalculator, Context $context)
    {
        $this->taxCalculator = $taxCalculator;
        $this->context = $context;
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
        float $totalCartPriceTaxIncl,
        float $surchargePercentage,
        float $surchargeLimit
    ): PaymentFeeData {
        $totalFeePriceTaxIncl = NumberUtility::times(
            $totalCartPriceTaxIncl,
            NumberUtility::divide($surchargePercentage, self::MAX_PERCENTAGE)
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
        float $totalCartPriceTaxIncl,
        float $surchargePercentage,
        float $surchargeFixedPriceTaxExcl,
        float $surchargeLimit
    ): PaymentFeeData {
        $surchargeFixedPriceTaxIncl = $this->taxCalculator->addTaxes($surchargeFixedPriceTaxExcl);

        $totalFeePriceTaxIncl = NumberUtility::plus(NumberUtility::times(
            $totalCartPriceTaxIncl,
            NumberUtility::divide($surchargePercentage, self::MAX_PERCENTAGE)
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
            NumberUtility::toPrecision($totalFeePriceTaxIncl, $this->context->getComputingPrecision()),
            NumberUtility::toPrecision($totalFeePriceTaxExcl, $this->context->getComputingPrecision()),
            $this->taxCalculator->getTotalRate(),
            $totalFeePriceTaxIncl > 0 && $totalFeePriceTaxExcl > 0
        );
    }
}
