<?php

namespace Mollie\Utility;

use Mollie\Adapter\Context;
use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;
use Tax;

class TaxUtility
{
    /** @var Context Context */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function addTax(float $totalFeePrice, Tax $tax): float
    {
        $taxCalculator = new \TaxCalculator([$tax]);

        $fee = (string) $taxCalculator->addTaxes($totalFeePrice);

        $calculatedPrice = new Number($fee);

        return (float) $calculatedPrice->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP);
    }

    public function removeTax(float $totalFeePrice, Tax $tax): float
    {
        $taxCalculator = new \TaxCalculator([$tax]);

        $fee = (string) $taxCalculator->removeTaxes($totalFeePrice);

        $calculatedPrice = new Number($fee);

        return (float) $calculatedPrice->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP);
    }
}
