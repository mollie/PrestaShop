<?php

namespace Mollie\Tests\Unit\Utility;

use Mollie\Adapter\Context;
use Mollie\Utility\TaxUtility;
use PHPUnit\Framework\TestCase;
use Tax;

class TaxUtilityTest extends TestCase
{
    /** @var Context */
    private $context;
    /** @var Tax */
    private $tax;

    public function setUp()
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
    }

    /**
     * @dataProvider getPricesDataProvider
     *
     * @param float $price
     * @param Tax $tax
     * @param float $priceWithoutTax
     * @param float $priceWithTax
     */
    public function testItCorrectlyCalculatesTaxes(float $price, Tax $tax, float $priceWithoutTax, float $priceWithTax): void
    {
        $this->context->method('getComputingPrecision')->willReturn(2);

        $taxUtility = new TaxUtility($this->context);

        $resultPriceWithTax = $taxUtility->addTax($price, $tax);
        $resultPriceWithoutTax = $taxUtility->removeTax($price, $tax);

        self::assertEquals($resultPriceWithTax, $priceWithTax);
        self::assertEquals($resultPriceWithoutTax, $priceWithoutTax);
    }

    public function getPricesDataProvider(): array
    {
        $this->tax = $this->createMock(Tax::class);
        $this->tax->rate = 10.00;

        return [
            'success' => [
                'price' => 10.00,
                'tax' => $this->tax,
                'priceWithoutTax' => 9.09,
                'priceWithTax' => 11,
            ],
            'negative numbers' => [
                'price' => -10.00,
                'tax' => $this->tax,
                'priceWithoutTax' => -9.09,
                'priceWithTax' => -11,
            ],
            'zero price' => [
                'price' => 0,
                'tax' => $this->tax,
                'priceWithoutTax' => 0,
                'priceWithTax' => 0,
            ],
        ];
    }
}
