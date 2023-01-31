<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;
use MolRecurringOrdersProduct;
use PrestaShopCollection;

interface RecurringOrdersProductRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?MolRecurringOrdersProduct
     */
    public function findOneBy(array $keyValueCriteria): ?MolRecurringOrdersProduct;

    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ?MolRecurringOrder
     */
    public function findAllBy(array $keyValueCriteria): ?PrestaShopCollection;
}
