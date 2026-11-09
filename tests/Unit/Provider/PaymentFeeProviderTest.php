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

namespace Mollie\Tests\Unit\Provider;

use Cart;
use Mollie;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Factory\ModuleFactory;
use Mollie\Logger\LoggerInterface;
use Mollie\Provider\PaymentFeeProvider;
use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Validator\PaymentFeeValidator;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;
use TaxCalculator;

class PaymentFeeProviderTest extends TestCase
{
    /** @var Context */
    private $context;

    /** @var MolPaymentMethod */
    private $molPaymentMethod;

    /** @var TaxCalculator */
    private $taxCalculator;

    /** @var ModuleFactory */
    private $moduleFactory;

    /** @var Mollie */
    private $module;

    /** @var PaymentFeeValidator */
    private $paymentFeeValidator;

    /** @var LoggerInterface */
    private $logger;

    /** @var TaxCalculatorProvider */
    private $taxCalculatorProvider;

    public function setUp()
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
        $this->molPaymentMethod = $this->createMock(MolPaymentMethod::class);
        $this->taxCalculator = $this->createMock(TaxCalculator::class);

        $this->module = $this->createMock(Mollie::class);
        $this->moduleFactory = $this->createMock(ModuleFactory::class);
        $this->moduleFactory->method('getModule')->willReturn($this->module);
        $this->paymentFeeValidator = $this->createMock(PaymentFeeValidator::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->taxCalculatorProvider = $this->createMock(TaxCalculatorProvider::class);
    }

    public function testItSuccessfullyProvidesFixedPaymentFee(): void
    {
        $this->molPaymentMethod->surcharge = Config::FEE_FIXED_FEE;
        $this->molPaymentMethod->surcharge_percentage = 0;
        $this->molPaymentMethod->surcharge_limit = 0;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 10;
        $this->molPaymentMethod->tax_rules_group_id = 1;

        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('getTotalRate')->willReturn(10);

        $this->taxCalculatorProvider->method('getCartTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFee($this->molPaymentMethod, 10);

        $this->assertEquals(11.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(10.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItSuccessfullyProvidesPercentagePaymentFee(): void
    {
        $this->molPaymentMethod->surcharge = Config::FEE_PERCENTAGE;
        $this->molPaymentMethod->surcharge_percentage = 10;
        $this->molPaymentMethod->surcharge_limit = 10;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 0;
        $this->molPaymentMethod->tax_rules_group_id = 1;

        $this->taxCalculator->method('removeTaxes')->willReturn(0.9);
        $this->taxCalculator->method('getTotalRate')->willReturn(10);

        $this->taxCalculatorProvider->method('getCartTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFee($this->molPaymentMethod, 10);

        $this->assertEquals(1.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(0.9, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItSuccessfullyProvidesPercentageAndFixedPricePaymentFee(): void
    {
        $this->molPaymentMethod->surcharge = Config::FEE_FIXED_FEE_AND_PERCENTAGE;
        $this->molPaymentMethod->surcharge_percentage = 10;
        $this->molPaymentMethod->surcharge_limit = 100;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 10;
        $this->molPaymentMethod->tax_rules_group_id = 1;

        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('removeTaxes')->willReturn(10.8);
        $this->taxCalculator->method('getTotalRate')->willReturn(10);

        $this->taxCalculatorProvider->method('getCartTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFee($this->molPaymentMethod, 10);

        $this->assertEquals(12.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(10.8, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(10.0, $result->getTaxRate());
        $this->assertEquals(true, $result->isActive());
    }

    public function testItSuccessfullyProvidesFeeSurchageTypeNotFound(): void
    {
        $this->molPaymentMethod->surcharge = 999;
        $this->molPaymentMethod->surcharge_percentage = 0;
        $this->molPaymentMethod->surcharge_limit = 0;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 0;
        $this->molPaymentMethod->tax_rules_group_id = 0;

        $this->taxCalculatorProvider->method('getCartTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFee($this->molPaymentMethod, 10);

        $this->assertEquals(0.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(0.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(0.0, $result->getTaxRate());
        $this->assertEquals(false, $result->isActive());
    }

    public function testItReturnsCorrectTextForPositivePaymentFee(): void
    {
        $this->module->method('l')
            ->with('Payment Fee: %1s', 'PaymentFeeProvider')
            ->willReturn('Payment Fee: %1s');

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFeeText(10.0);

        $this->assertEquals('Payment Fee: %1s', $result);
    }

    public function testItReturnsCorrectTextForNegativePaymentFee(): void
    {
        $this->module->method('l')
            ->with('Discount: %1s', 'PaymentFeeProvider')
            ->willReturn('Discount: %1s');

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFeeText(-5.0);

        $this->assertEquals('Discount: %1s', $result);
    }

    public function testItReturnsEmptyStringForZeroPaymentFee(): void
    {
        $this->module->method('l')
            ->withAnyParameters()
            ->willReturn('');

        $paymentFeeProvider = $this->createProvider();

        $result = $paymentFeeProvider->getPaymentFeeText(0.0);

        $this->assertEquals('', $result);
    }

    public function testItTaxesTheFeeByTheGivenCart(): void
    {
        $this->setFixedFeeMethod();

        $cart = $this->createMock(Cart::class);

        $this->context->expects($this->never())->method('getCart');
        $this->taxCalculatorProvider->expects($this->once())
            ->method('getCartTaxCalculator')
            ->with(12, $cart)
            ->willReturn($this->taxCalculator);

        $this->createProvider()->getPaymentFee($this->molPaymentMethod, 10, $cart);
    }

    public function testItTaxesTheFeeByTheContextCartWhenNoCartIsGiven(): void
    {
        $this->setFixedFeeMethod();

        $contextCart = $this->createMock(Cart::class);

        $this->context->method('getCart')->willReturn($contextCart);
        $this->taxCalculatorProvider->expects($this->once())
            ->method('getCartTaxCalculator')
            ->with(12, $contextCart)
            ->willReturn($this->taxCalculator);

        $this->createProvider()->getPaymentFee($this->molPaymentMethod, 10);
    }

    private function setFixedFeeMethod(): void
    {
        $this->molPaymentMethod->surcharge = Config::FEE_FIXED_FEE;
        $this->molPaymentMethod->surcharge_percentage = 0;
        $this->molPaymentMethod->surcharge_limit = 0;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 2;
        $this->molPaymentMethod->tax_rules_group_id = 12;

        $this->context->method('getComputingPrecision')->willReturn(2);
    }

    private function createProvider(): PaymentFeeProvider
    {
        return new PaymentFeeProvider(
            $this->context,
            $this->moduleFactory,
            $this->paymentFeeValidator,
            $this->logger,
            $this->taxCalculatorProvider
        );
    }
}
