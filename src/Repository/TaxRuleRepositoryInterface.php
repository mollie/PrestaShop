<?php

namespace Mollie\Repository;

interface TaxRuleRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getTaxRule(int $taxRulesGroupId, int $countryId, int $stateId): array;
}
