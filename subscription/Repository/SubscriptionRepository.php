<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolSubRecurringOrder;

class SubscriptionRepository extends AbstractRepository implements SubscriptionRepositoryInterface
{
    public function findOneBy(array $keyValueCriteria): ?MolSubRecurringOrder
    {
        /** @var ?MolSubRecurringOrder $result */
        $result = parent::findOneBy($keyValueCriteria);

        return $result;
    }
}
