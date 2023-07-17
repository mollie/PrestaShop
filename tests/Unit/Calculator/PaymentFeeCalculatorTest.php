<?php

namespace Calculator;

namespace Unit\Calculator;

use Mollie\Adapter\LegacyContext;
use Mollie\Calculator\PaymentFeeCalculator;
use PHPUnit\Framework\TestCase;
use TaxCalculator;

class PaymentFeeCalculatorTest extends TestCase
{
    /** @var TaxCalculator */
    private $taxCalculator;
    /** @var LegacyContext */
    private $context;

    public function setUp()
    {
        parent::setUp();

        $this->taxCalculator = $this->createMock(TaxCalculator::class);
        $this->context = $this->createMock(LegacyContext::class);
    }

    public function testItCalculatesFixedFee()
    {
        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('getTotalRate')->willReturn(10.0);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeCalculator = new PaymentFeeCalculator($this->taxCalculator, $this->context);

        $result = $paymentFeeCalculator->calculateFixedFee(10);

        $this->assertEquals(11.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(10.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItCalculatesPercentageFeeWithoutReachedLimit()
    {
        $this->taxCalculator->method('removeTaxes')->willReturn(1.0);
        $this->taxCalculator->method('getTotalRate')->willReturn(10.0);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeCalculator = new PaymentFeeCalculator($this->taxCalculator, $this->context);

        $result = $paymentFeeCalculator->calculatePercentageFee(
            11,
            10,
            10
        );

        $this->assertEquals(1.1, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(1.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItCalculatesPercentageFeeWithReachedLimit()
    {
        $this->taxCalculator->method('removeTaxes')->willReturn(10.0);
        $this->taxCalculator->method('getTotalRate')->willReturn(10.0);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeCalculator = new PaymentFeeCalculator($this->taxCalculator, $this->context);

        $result = $paymentFeeCalculator->calculatePercentageFee(
            200,
            10,
            11
        );

        $this->assertEquals(11.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(10.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItCalculatesPercentageAndFixedPriceFeeWithoutReachedLimit()
    {
        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('removeTaxes')->willReturn(18.9);
        $this->taxCalculator->method('getTotalRate')->willReturn(10.0);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeCalculator = new PaymentFeeCalculator($this->taxCalculator, $this->context);

        $result = $paymentFeeCalculator->calculatePercentageAndFixedPriceFee(
            100,
            10,
            10,
            100
        );

        $this->assertEquals(21.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(18.9, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItCalculatesPercentageAndFixedPriceFeeWithReachedLimit()
    {
        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('removeTaxes')->willReturn(10.0);
        $this->taxCalculator->method('getTotalRate')->willReturn(10.0);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeCalculator = new PaymentFeeCalculator($this->taxCalculator, $this->context);

        $result = $paymentFeeCalculator->calculatePercentageAndFixedPriceFee(
            100,
            10,
            10,
            11
        );

        $this->assertEquals(11.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(10.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }
}
