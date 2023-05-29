<?php

namespace Mollie\Tests\Unit\Provider;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\FailedToProvideTaxException;
use Mollie\Provider\TaxProvider;
use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Tax;
use TaxRule;

class TaxProviderTest extends TestCase
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

    public function testItSuccessfullyProvidesTax(): void
    {
        $taxRule = $this->createMock(TaxRule::class);
        $taxRule->id = 1;

        $tax = $this->createMock(Tax::class);
        $tax->id = 1;

        $this->taxRuleRepository->method('findOneBy')->willReturn($taxRule);
        $this->taxRepository->method('findOneBy')->willReturn($tax);

        $taxProvider = new TaxProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $taxProvider->getTax(1, 1, 1);
    }

    public function testItUnsuccessfullyProvidesTaxFailedToFindTaxRule(): void
    {
        $this->taxRuleRepository->method('findOneBy')->willReturn(null);

        $taxProvider = new TaxProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $this->expectExceptionCode(ExceptionCode::FAILED_TO_FIND_TAX_RULE);
        $this->expectException(FailedToProvideTaxException::class);

        $taxProvider->getTax(1, 1, 1);
    }

    public function testItUnsuccessfullyProvidesTaxFailedToFindTax(): void
    {
        $taxRule = $this->createMock(TaxRule::class);
        $taxRule->id = 1;

        $this->taxRuleRepository->method('findOneBy')->willReturn($taxRule);
        $this->taxRepository->method('findOneBy')->willReturn(null);

        $taxProvider = new TaxProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $this->expectExceptionCode(ExceptionCode::FAILED_TO_FIND_TAX);
        $this->expectException(FailedToProvideTaxException::class);

        $taxProvider->getTax(1, 1, 1);
    }
}
