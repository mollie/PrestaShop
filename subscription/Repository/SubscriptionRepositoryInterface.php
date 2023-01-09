<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolSubRecurringOrder;
use ObjectModel;

interface SubscriptionRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ObjectModel|null
     */
    public function findOneBy(array $keyValueCriteria): ?MolSubRecurringOrder;
}
