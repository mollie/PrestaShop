<?php

namespace Mollie\Subscription\Repository;

class SpecificPriceRepository extends AbstractRepository implements SpecificPriceRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\SpecificPrice::class);
    }
}
