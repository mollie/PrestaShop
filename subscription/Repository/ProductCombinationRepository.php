<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

class ProductCombinationRepository
{
    public function getIds(int $combinationId): array
    {
        $query = new \DbQuery();
        $query
            ->select('combination.id_attribute')
            ->from('product_attribute_combination', 'combination')
            ->where('combination.id_product_attribute = ' . $combinationId);

        return \Db::getInstance()->executeS($query) ?: [];
    }
}
