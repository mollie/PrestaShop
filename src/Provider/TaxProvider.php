<?php

namespace Mollie\Provider;

use Mollie\Exception\Code\ExceptionCode;
use Mollie\Exception\FailedToProvideTaxException;
use Mollie\Repository\TaxRepositoryInterface;
use Mollie\Repository\TaxRuleRepositoryInterface;
use Tax;
use TaxRule;

class TaxProvider
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
     * @return Tax
     *
     * @throws FailedToProvideTaxException
     */
    public function getTax(int $taxRulesGroupId, int $countryId, int $stateId): Tax
    {
        /** @var TaxRule|null $taxRule */
        $taxRule = $this->taxRuleRepository->findOneBy([
            'id_tax_rules_group' => $taxRulesGroupId,
            'id_country' => $countryId,
            'id_state' => $stateId,
        ]);

        if (!$taxRule || !$taxRule->id) {
            throw new FailedToProvideTaxException('Failed to find tax rules.', ExceptionCode::FAILED_TO_FIND_TAX_RULE);
        }

        /** @var Tax|null $tax */
        $tax = $this->taxRepository->findOneBy([
            'id_tax' => $taxRule->id_tax,
        ]);

        if (!$tax || !$tax->id) {
            throw new FailedToProvideTaxException('Failed to find tax.', ExceptionCode::FAILED_TO_FIND_TAX);
        }

        return $tax;
    }
}
