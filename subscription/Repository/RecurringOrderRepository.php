<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;

class RecurringOrderRepository extends AbstractRepository implements RecurringOrderRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrder
    {
        /** @var ?MolRecurringOrder $result */
        $result = parent::findOneBy($keyValueCriteria);

        return $result;
    }
}
