<?php

namespace Mollie\Provider;

use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use Tax;
use TaxCalculator;

class TaxCalculatorProvider
{
    /** @var TaxRuleRepositoryInterface */
    private $taxRuleRepository;
    /** @var TaxRepositoryInterface */
    private $taxRepository;

    public function __construct(
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxRepositoryInterface $taxRepository
    ) {
        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxRepository = $taxRepository;
    }

    /**
     * @param int $taxRulesGroupId
     * @param int $countryId
     * @param int $stateId
     *
     * @return TaxCalculator
     */
    public function getTaxCalculator(int $taxRulesGroupId, int $countryId, int $stateId): TaxCalculator
    {
        $taxRules = $this->taxRuleRepository->getTaxRule(
            $taxRulesGroupId,
            $countryId,
            $stateId
        );

        $taxes = [];
        $behavior = 0;
        $firstRow = true;

        foreach ($taxRules as $taxRule) {
            /** @var Tax|null $tax */
            $tax = $this->taxRepository->findOneBy([
                'id_tax' => $taxRule['id_tax'],
            ]);

            if (!$tax || !$tax->id) {
                continue;
            }

            $taxes[] = $tax;

            // the applied behavior correspond to the most specific rules
            if ($firstRow) {
                $behavior = (int) $taxRule['behavior'];
                $firstRow = false;
            }

            if ((int) $taxRule['behavior'] === 0) {
                break;
            }
        }

        return new TaxCalculator($taxes, $behavior);
    }
}
