<?php

namespace Mollie\Repository;

class TaxRuleRepository extends AbstractRepository implements TaxRuleRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\TaxRule::class);
    }
}
