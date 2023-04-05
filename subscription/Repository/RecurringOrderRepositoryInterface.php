<?php

declare(strict_types=1);

namespace Mollie\Subscription\Repository;

use MolRecurringOrder;
use PrestaShopCollection;
use PrestaShopException;

interface RecurringOrderRepositoryInterface
{
//    TODO add return types for all repositories

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

    /**
     * @return PrestaShopCollection
     *
     * @throws PrestaShopException
     */
    public function findAll();
}
