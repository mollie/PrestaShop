<?php

namespace Mollie\Repository;

use ObjectModel;

interface ReadOnlyRepositoryInterface
{
    /**
     * @param array $keyValueCriteria - e.g [ 'id_cart' => 5 ]
     *
     * @return ObjectModel
     */
    public function findOneBy(array $keyValueCriteria);
}