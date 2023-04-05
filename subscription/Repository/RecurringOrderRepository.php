<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;
use PrestaShopCollection;

class RecurringOrderRepository extends AbstractRepository implements RecurringOrderRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrder
    {
        /** @var ?MolRecurringOrder $result */
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
