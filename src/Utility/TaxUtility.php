<?php

namespace Mollie\Utility;

use Tax;
use TaxCalculator;

class TaxUtility
{
    public function addTax(float $price, Tax $tax): float
    {
        return (new TaxCalculator([$tax]))->addTaxes($price);
    }

    public function removeTax(float $price, Tax $tax): float
    {
        return (new TaxCalculator([$tax]))->removeTaxes($price);
    }
}
