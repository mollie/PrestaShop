<?php

namespace Mollie\Tests\Unit\Provider;

use Address;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\FailedToProvidePaymentFeeException;
use Mollie\Provider\PaymentFeeProvider;
use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Repository\AddressRepositoryInterface;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;
use TaxCalculator;

class PaymentFeeProviderTest extends TestCase
{
    /** @var Context */
    private $context;
    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var TaxCalculatorProvider */
    private $taxCalculatorProvider;

    /** @var MolPaymentMethod */
    private $molPaymentMethod;

    /** @var Address */
    private $address;

    /** @var TaxCalculator */
    private $taxCalculator;

    public function setUp()
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->taxCalculatorProvider = $this->createMock(TaxCalculatorProvider::class);

        $this->molPaymentMethod = $this->createMock(MolPaymentMethod::class);
        $this->address = $this->createMock(Address::class);
        $this->taxCalculator = $this->createMock(TaxCalculator::class);
    }

    public function testItSuccessfullyProvidesFixedPaymentFee(): void
    {
        $this->molPaymentMethod->surcharge = Config::FEE_FIXED_FEE;
        $this->molPaymentMethod->surcharge_percentage = 0;
        $this->molPaymentMethod->surcharge_limit = 0;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 10;
        $this->molPaymentMethod->tax_rules_group_id = 1;

        $this->address = $this->createMock(Address::class);

        $this->address->id = 1;
        $this->address->id_country = 1;
        $this->address->id_state = 0;

        $this->addressRepository->method('findOneBy')->willReturn($this->address);

        $this->taxCalculator = $this->createMock(TaxCalculator::class);

        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('getTotalRate')->willReturn(10);

        $this->taxCalculatorProvider->method('getTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getCustomerAddressInvoiceId')->willReturn(1);
        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->addressRepository,
            $this->taxCalculatorProvider
        );

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

        $this->address->id = 1;
        $this->address->id_country = 1;
        $this->address->id_state = 0;

        $this->addressRepository->method('findOneBy')->willReturn($this->address);

        $this->taxCalculator->method('removeTaxes')->willReturn(0.9);
        $this->taxCalculator->method('getTotalRate')->willReturn(10);

        $this->taxCalculatorProvider->method('getTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getCustomerAddressInvoiceId')->willReturn(1);
        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->addressRepository,
            $this->taxCalculatorProvider
        );

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

        $this->address->id = 1;
        $this->address->id_country = 1;
        $this->address->id_state = 0;

        $this->addressRepository->method('findOneBy')->willReturn($this->address);

        $this->taxCalculator->method('addTaxes')->willReturn(11.0);
        $this->taxCalculator->method('removeTaxes')->willReturn(10.8);
        $this->taxCalculator->method('getTotalRate')->willReturn(10);

        $this->taxCalculatorProvider->method('getTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getCustomerAddressInvoiceId')->willReturn(1);
        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->addressRepository,
            $this->taxCalculatorProvider
        );

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

        $this->address->id = 1;
        $this->address->id_country = 1;
        $this->address->id_state = 0;

        $this->addressRepository->method('findOneBy')->willReturn($this->address);

        $this->taxCalculatorProvider->method('getTaxCalculator')->willReturn($this->taxCalculator);

        $this->context->method('getCustomerAddressInvoiceId')->willReturn(1);
        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->addressRepository,
            $this->taxCalculatorProvider
        );

        $result = $paymentFeeProvider->getPaymentFee($this->molPaymentMethod, 10);

        $this->assertEquals(0.0, $result->getPaymentFeeTaxIncl());
        $this->assertEquals(0.0, $result->getPaymentFeeTaxExcl());
        $this->assertEquals(0.0, $result->getTaxRate());
        $this->assertEquals(false, $result->isActive());
    }

    public function testItUnsuccessfullyProvidesFeeAddressNotFound(): void
    {
        $this->molPaymentMethod->surcharge_percentage = 0;
        $this->molPaymentMethod->surcharge_limit = 0;
        $this->molPaymentMethod->surcharge_fixed_amount_tax_excl = 0;

        $this->addressRepository->method('findOneBy')->willReturn(null);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->addressRepository,
            $this->taxCalculatorProvider
        );

        $this->expectException(FailedToProvidePaymentFeeException::class);
        $this->expectExceptionCode(ExceptionCode::FAILED_TO_FIND_CUSTOMER_ADDRESS);

        $paymentFeeProvider->getPaymentFee($this->molPaymentMethod, 10);
    }
}
