<?php

namespace Mollie\Tests\Unit\Provider;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\FailedToProvideTaxCalculatorException;
use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Tax;
use TaxRule;

class TaxCalculatorProviderTest extends TestCase
{
    /** @var TaxRuleRepositoryInterface */
    private $taxRuleRepository;
    /** @var TaxRepositoryInterface */
    private $taxRepository;

    public function setUp()
    {
        parent::setUp();

        $this->taxRuleRepository = $this->createMock(TaxRuleRepositoryInterface::class);
        $this->taxRepository = $this->createMock(TaxRepositoryInterface::class);
    }

    public function testItSuccessfullyProvidesTaxCalculator(): void
    {
        $taxRule = ['id_tax' => 1, 'behavior' => 0];

        $tax = $this->createMock(Tax::class);
        $tax->id = 1;

        $this->taxRuleRepository->method('getTaxRule')->willReturn([$taxRule]);
        $this->taxRepository->method('findOneBy')->willReturn($tax);

        $taxProvider = new TaxCalculatorProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $taxProvider->getTaxCalculator(1, 1, 1);
    }

    public function testItUnsuccessfullyProvidesTaxCalculatorFailedToFindTaxRule(): void
    {
        $this->taxRuleRepository->method('getTaxRule')->willReturn([]);

        $taxProvider = new TaxCalculatorProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $this->expectExceptionCode(ExceptionCode::FAILED_TO_FIND_TAX_RULES);
        $this->expectException(FailedToProvideTaxCalculatorException::class);

        $taxProvider->getTaxCalculator(1, 1, 1);
    }

    public function testItUnsuccessfullyProvidesTaxCalculatorFailedToFindTax(): void
    {
        $taxRule = ['id_tax' => 1, 'behavior' => 0];

        $this->taxRuleRepository->method('getTaxRule')->willReturn([$taxRule]);
        $this->taxRepository->method('findOneBy')->willReturn(null);

        $taxProvider = new TaxCalculatorProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $this->expectExceptionCode(ExceptionCode::FAILED_TO_FIND_TAX);
        $this->expectException(FailedToProvideTaxCalculatorException::class);

        $taxProvider->getTaxCalculator(1, 1, 1);
    }
}
