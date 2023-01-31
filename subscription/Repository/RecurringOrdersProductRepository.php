<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrdersProduct;
use PrestaShopCollection;

class RecurringOrdersProductRepository extends AbstractRepository implements RecurringOrdersProductRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrdersProduct
    {
        /** @var ?MolRecurringOrdersProduct $result */
        $result = parent::findOneBy($keyValueCriteria);

        return $result;
    }

    public function findAllBy(array $keyValueCriteria): ?PrestaShopCollection
    {
        /** @var ?PrestaShopCollection $result */
        $result = parent::findAllBy($keyValueCriteria);

        return $result;
    }
}
