<?php

namespace Mollie\Subscription\Repository;

class OrderDetailRepository extends AbstractRepository implements OrderDetailRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\OrderDetail::class);
    }
}
