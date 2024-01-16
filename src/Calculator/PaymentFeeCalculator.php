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

namespace Mollie\Calculator;

use Mollie\Adapter\Context;
use Mollie\DTO\PaymentFeeData;
use Mollie\Utility\NumberUtility;
use TaxCalculator;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

        return $this->buildPaymentFee(
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

        return $this->buildPaymentFee(
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

        return $this->buildPaymentFee(
            $totalFeePriceTaxIncl,
            $totalFeePriceTaxExcl
        );
    }

    private function calculateSurchargeMaxValue(float $surchargeMaxValue): PaymentFeeData
    {
        $totalFeePriceTaxIncl = $surchargeMaxValue;
        $totalFeePriceTaxExcl = $this->taxCalculator->removeTaxes($totalFeePriceTaxIncl);

        return $this->buildPaymentFee(
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

    private function buildPaymentFee(
        float $totalFeePriceTaxIncl,
        float $totalFeePriceTaxExcl
    ): PaymentFeeData {
        $isPaymentFeeActive = $totalFeePriceTaxIncl > 0 && $totalFeePriceTaxExcl > 0;

        return new PaymentFeeData(
            NumberUtility::toPrecision($totalFeePriceTaxIncl, $this->context->getComputingPrecision()),
            NumberUtility::toPrecision($totalFeePriceTaxExcl, $this->context->getComputingPrecision()),
            $this->taxCalculator->getTotalRate(),
            $isPaymentFeeActive
        );
    }
}
