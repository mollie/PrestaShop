<?php

namespace Mollie\Repository;

interface TaxRulesGroupRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getTaxRulesGroups(int $shopId): array;
}
