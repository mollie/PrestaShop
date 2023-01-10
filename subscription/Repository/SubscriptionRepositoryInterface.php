<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolSubRecurringOrder;

interface SubscriptionRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?MolSubRecurringOrder
     */
    public function findOneBy(array $keyValueCriteria): ?MolSubRecurringOrder;
}
