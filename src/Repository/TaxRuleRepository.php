<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

class TaxRuleRepository extends AbstractRepository implements TaxRuleRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\TaxRule::class);
    }

    public function getTaxRule(int $taxRulesGroupId, int $countryId, int $stateId): array
    {
        $query = new DbQuery();

        $query->select('tr.id_tax, tr.behavior')
            ->from('tax_rule', 'tr')
            ->where('tr.id_tax_rules_group = ' . $taxRulesGroupId)
            ->where('tr.id_country = ' . $countryId)
            ->where('tr.id_state IN (0, ' . $stateId . ')')
            ->orderBy('tr.id_state DESC');

        $result = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($query);

        if (empty($result)) {
            return [];
        }

        return $result;
    }
}
