<?php

use Mollie\Utility\CalculationUtility;
use PHPUnit\Framework\TestCase;

class CalculationUtilityTest extends TestCase
{

    /**
     * @dataProvider remainingPriceDataProvider
     * @param $totalPrice
     * @param $shippingPrice
     * @param $wrappingPrice
     * @param $result
     */
    public function testGetCartRemainingPrice($totalPrice, $shippingPrice, $wrappingPrice, $result)
    {
        $remainingPrice = CalculationUtility::getCartRemainingPrice($totalPrice, $shippingPrice, $wrappingPrice);

        $this->assertEquals($result, $remainingPrice);
    }

    /**
     * @dataProvider unitPriceNoTaxDataProvider
     * @param $unitPrice
     * @param $targetVat
     * @param $result
     */
    public function testGetUnitPriceNoTax($unitPrice, $targetVat, $result)
    {
        $unitPriceNoTax = CalculationUtility::getUnitPriceNoTax($unitPrice, $targetVat);
        $roundedPrice = round($unitPriceNoTax, 2);

        $this->assertEquals($result, $roundedPrice);
    }

    /**
     * @dataProvider actualVatRateDataProvider
     * @param $unitPrice
     * @param $quantity
     * @param $unitPriceNoTax
     */
    public function testGetActualVatRate($unitPrice, $quantity, $unitPriceNoTax, $result)
    {
        $vatRate = CalculationUtility::getActualVatRate($unitPrice, $unitPriceNoTax, $quantity);
        $vatRate = round($vatRate, 0);

        $this->assertEquals($result, $vatRate);
    }

    public function remainingPriceDataProvider()
    {
        return [
            'case1' =>
                [
                    'totalPrice' => 10,
                    'shippingPrice' => 1,
                    'wrappingPrice' => 1,
                    'result' => 8
                ],
            'case2' =>
                [
                    'totalPrice' => 150,
                    'shippingPrice' => 100,
                    'wrappingPrice' => 6,
                    'result' => 44
                ],
            'case3' =>
                [
                    'totalPrice' => 30,
                    'shippingPrice' => 0,
                    'wrappingPrice' => 0,
                    'result' => 30
                ]
        ];
    }

    public function unitPriceNoTaxDataProvider()
    {
        return [
            'case1' =>
                [
                    'unitPrice' => 100,
                    'targetVat' => 21,
                    'result' => 82.64
                ],
            'case2' =>
                [
                    'unitPrice' => 70,
                    'targetVat' => 12,
                    'result' => 62.5
                ],
            'case3' =>
                [
                    'unitPrice' => 1234,
                    'targetVat' => 24,
                    'result' => 995.16
                ]
        ];
    }

    public function actualVatRateDataProvider()
    {
        return [
            'case1' =>
                [
                    'unitPrice' => 100,
                    'quantity' => 1,
                    'unitPriceNoTax' => 82.64,
                    'result' => 21
                ],
            'case2' =>
                [
                    'unitPrice' => 100,
                    'quantity' => 5,
                    'unitPriceNoTax' => 86.21,
                    'result' => 16
                ],
            'case3' =>
                [
                    'unitPrice' => 2,
                    'quantity' => 1,
                    'unitPriceNoTax' => 1.67,
                    'result' => 20
                ]
        ];
    }
}
