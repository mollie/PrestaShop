<?php

namespace Mollie\Tests\Unit\Provider;

use Address;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Provider\PaymentFeeProvider;
use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Repository\AddressRepositoryInterface;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;
use Tax;
use TaxCalculator;

class PaymentFeeProviderTest extends TestCase
{
    /** @var Context */
    private $context;
    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var TaxCalculatorProvider */
    private $taxProvider;

    public function setUp()
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->taxProvider = $this->createMock(TaxCalculatorProvider::class);
    }

    /**
     * @dataProvider paymentFeeDataProvider
     */
    public function testItProvidesPaymentFee(
        array $paymentMethod,
        array $taxCalculatorResults,
        float $totalCartPrice,
        array $expectedResult
    ): void {
        $molPaymentMethod = $this->createMock(MolPaymentMethod::class);

        $molPaymentMethod->surcharge = $paymentMethod['surcharge'];
        $molPaymentMethod->surcharge_percentage = $paymentMethod['surcharge_percentage'];
        $molPaymentMethod->surcharge_limit = $paymentMethod['surcharge_limit'];
        $molPaymentMethod->surcharge_fixed_amount_tax_excl = $paymentMethod['surcharge_fixed_amount_tax_excl'];
        $molPaymentMethod->tax_rules_group_id = 1; // NOTE: It's always the same in the test

        $address = $this->createMock(Address::class);

        $address->id = 1;
        $address->id_country = 1;
        $address->id_state = 0;

        $this->addressRepository->method('findOneBy')->willReturn($address);

        $taxCalculator = $this->createMock(TaxCalculator::class);

        $taxCalculator->method('addTaxes')->willReturn($taxCalculatorResults['addTaxResult']);
        $taxCalculator->method('removeTaxes')->willReturn($taxCalculatorResults['removeTaxResult']);

        $this->taxProvider->method('getTaxCalculator')->willReturn($taxCalculator);

        $this->context->method('getCustomerAddressInvoiceId')->willReturn(1);
        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->addressRepository,
            $this->taxProvider
        );

        $result = $paymentFeeProvider->getPaymentFee($molPaymentMethod, $totalCartPrice);

        $this->assertEquals($result->getPaymentFeeTaxIncl(), $expectedResult['paymentFeeTaxIncl']);
        $this->assertEquals($result->getPaymentFeeTaxExcl(), $expectedResult['paymentFeeTaxExcl']);
        $this->assertEquals($result->isActive(), $expectedResult['active']);
    }

    public function paymentFeeDataProvider(): array
    {
        return [
            'success with fixed price' => [
                'paymentMethod' => [
                    'surcharge' => Config::FEE_FIXED_FEE,
                    'surcharge_percentage' => '0',
                    'surcharge_limit' => '0',
                    'surcharge_fixed_amount_tax_excl' => 10.00,
                    'tax_rules_group_id' => 1,
                ],
                'taxCalculatorResults' => [
                    'addTaxResult' => 11.00,
                    'removeTaxResult' => 0.00,
                ],
                'totalCartPrice' => 10,
                'expectedResult' => [
                    'paymentFeeTaxIncl' => 11.00,
                    'paymentFeeTaxExcl' => 10.00,
                    'active' => true,
                ],
            ],
            'success with percentage price' => [
                'paymentMethod' => [
                    'surcharge' => Config::FEE_PERCENTAGE,
                    'surcharge_percentage' => '10',
                    'surcharge_limit' => 5.00,
                    'surcharge_fixed_amount_tax_excl' => 0,
                    'tax_rules_group_id' => 1,
                ],
                'taxCalculatorResults' => [
                    'addTaxResult' => 1.1,
                    'removeTaxResult' => 0.00,
                ],
                'totalCartPrice' => 10,
                'expectedResult' => [
                    'paymentFeeTaxIncl' => 1.1,
                    'paymentFeeTaxExcl' => 1.0,
                    'active' => true,
                ],
            ],
            'success with percentage price with reached limit' => [
                'paymentMethod' => [
                    'surcharge' => Config::FEE_PERCENTAGE,
                    'surcharge_percentage' => '10',
                    'surcharge_limit' => 11.00,
                    'surcharge_fixed_amount_tax_excl' => 0,
                    'tax_rules_group_id' => 1,
                ],
                'taxCalculatorResults' => [
                    'addTaxResult' => 22.00,
                    'removeTaxResult' => 10.00,
                ],
                'totalCartPrice' => 200,
                'expectedResult' => [
                    'paymentFeeTaxIncl' => 11.00,
                    'paymentFeeTaxExcl' => 10.00,
                    'active' => true,
                ],
            ],
            'success with fee and percentage price' => [
                'paymentMethod' => [
                    'surcharge' => Config::FEE_FIXED_FEE_AND_PERCENTAGE,
                    'surcharge_percentage' => '10',
                    'surcharge_limit' => 100.00,
                    'surcharge_fixed_amount_tax_excl' => 10.00,
                    'tax_rules_group_id' => 1,
                ],
                'taxCalculatorResults' => [
                    'addTaxResult' => 22.00,
                    'removeTaxResult' => 0.00,
                ],
                'totalCartPrice' => 100,
                'expectedResult' => [
                    'paymentFeeTaxIncl' => 22.00,
                    'paymentFeeTaxExcl' => 20.00,
                    'active' => true,
                ],
            ],
        ];
    }
}
