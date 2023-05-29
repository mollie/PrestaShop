<?php

namespace Mollie\Tests\Unit\Provider;

use Address;
use Mollie\Adapter\Context;
use Mollie\Config\Config;
use Mollie\Provider\PaymentFeeProvider;
use Mollie\Provider\TaxProvider;
use Mollie\Repository\AddressRepositoryInterface;
use Mollie\Utility\TaxUtility;
use MolPaymentMethod;
use PHPUnit\Framework\TestCase;
use Tax;

class PaymentFeeProviderTest extends TestCase
{
    /** @var Context */
    private $context;
    /** @var TaxUtility */
    private $taxUtility;
    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var TaxProvider */
    private $taxProvider;

    public function setUp()
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
        $this->taxUtility = $this->createMock(TaxUtility::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->taxProvider = $this->createMock(TaxProvider::class);
    }

    /**
     * @dataProvider paymentFeeDataProvider
     */
    public function testItProvidesPaymentFee(
        array $paymentMethod,
        array $taxUtility,
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

        $tax = $this->createMock(Tax::class);
        $tax->id = 1;

        $this->taxUtility->method('addTax')->willReturn($taxUtility['addTaxResult']);
        $this->taxUtility->method('removeTax')->willReturn($taxUtility['removeTaxResult']);

        $this->taxProvider->method('getTax')->willReturn($tax);
        $this->context->method('getCustomerAddressInvoiceId')->willReturn(1);
        $this->context->method('getComputingPrecision')->willReturn(2);

        $paymentFeeProvider = new PaymentFeeProvider(
            $this->context,
            $this->taxUtility,
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
                'taxUtility' => [
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
                'taxUtility' => [
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
                'taxUtility' => [
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
                'taxUtility' => [
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
