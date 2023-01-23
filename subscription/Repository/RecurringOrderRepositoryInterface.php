<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;

interface RecurringOrderRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?MolRecurringOrder
     */
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrder;
}
