<?php

namespace Mollie\Repository;

use Db;
use DbQuery;

class TaxRulesGroupRepository extends AbstractRepository implements TaxRulesGroupRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\TaxRulesGroup::class);
    }

    public function getTaxRulesGroups(int $shopId): array
    {
        $query = new DbQuery();

        $query->select('trg.id_tax_rules_group as id, trg.name')
            ->from('tax_rules_group', 'trg')
            ->leftJoin('tax_rules_group_shop', 'trgs', 'trgs.id_tax_rules_group = trg.id_tax_rules_group')
            ->where('trg.deleted = 0')
            ->where('trg.active = 1')
            ->where('trgs.id_shop = ' . $shopId);

        $result = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($query);

        if (empty($result)) {
            return [];
        }

        return $result;
    }
}
