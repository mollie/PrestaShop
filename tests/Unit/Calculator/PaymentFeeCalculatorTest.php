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

namespace Unit\Calculator;

use Mollie\Adapter\Context;
use Mollie\Calculator\PaymentFeeCalculator;
use PHPUnit\Framework\TestCase;
use TaxCalculator;

class PaymentFeeCalculatorTest extends TestCase
{
    /** @var TaxCalculator */
    private $taxCalculator;
    /** @var Context */
    private $context;

    public function setUp()
    {
        parent::setUp();

        $this->taxCalculator = $this->createMock(TaxCalculator::class);
        $this->context = $this->createMock(Context::class);
    }

    public function testItCalculatesFixedFee(): void
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

    public function testItCalculatesPercentageFeeWithoutReachedLimit(): void
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

    public function testItCalculatesPercentageFeeWithReachedLimit(): void
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

    public function testItCalculatesPercentageAndFixedPriceFeeWithoutReachedLimit(): void
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

    public function testItCalculatesPercentageAndFixedPriceFeeWithReachedLimit(): void
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
