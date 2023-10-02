<?php

namespace Mollie\Repository;

class CarrierRepository extends AbstractRepository implements CarrierRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Carrier::class);
    }
}
