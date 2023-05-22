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

    public function addTax(float $price, Tax $tax): float
    {
        $taxCalculator = new \TaxCalculator([$tax]);

        $calculatedPrice = new Number((string) $taxCalculator->addTaxes($price));

        return (float) $calculatedPrice->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP);
    }

    public function removeTax(float $price, Tax $tax): float
    {
        $taxCalculator = new \TaxCalculator([$tax]);

        $calculatedPrice = new Number((string) $taxCalculator->removeTaxes($price));

        return (float) $calculatedPrice->toPrecision($this->context->getComputingPrecision(), Rounding::ROUND_HALF_UP);
    }
}
