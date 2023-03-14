<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;
use PrestaShopCollection;

interface RecurringOrderRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?MolRecurringOrder
     */
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrder;

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?PrestaShopCollection
     */
    public function findAllBy(array $keyValueCriteria): ?PrestaShopCollection;
}
