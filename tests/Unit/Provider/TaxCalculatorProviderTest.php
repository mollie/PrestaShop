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

use Mollie\Provider\TaxCalculatorProvider;
use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Tax;

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
        $tax->rate = 10;

        $this->taxRuleRepository->method('getTaxRule')->willReturn([$taxRule]);
        $this->taxRepository->method('findOneBy')->willReturn($tax);

        $taxProvider = new TaxCalculatorProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $result = $taxProvider->getTaxCalculator(1, 1, 1);

        $this->assertEquals(10.00, $result->getTotalRate());
    }

    public function testItUnsuccessfullyProvidesTaxCalculatorFailedToFindTaxRule(): void
    {
        $this->taxRuleRepository->method('getTaxRule')->willReturn([]);

        $taxProvider = new TaxCalculatorProvider(
            $this->taxRuleRepository,
            $this->taxRepository
        );

        $result = $taxProvider->getTaxCalculator(1, 1, 1);

        $this->assertEquals(0.00, $result->getTotalRate());
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

        $result = $taxProvider->getTaxCalculator(1, 1, 1);

        $this->assertEquals(0.00, $result->getTotalRate());
    }
}
