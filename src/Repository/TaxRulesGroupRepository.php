<?php

namespace Mollie\Repository;

class TaxRulesGroupRepository extends AbstractRepository implements TaxRulesGroupRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\TaxRulesGroup::class);
    }
}
