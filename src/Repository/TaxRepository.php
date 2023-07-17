<?php

namespace Mollie\Repository;

class TaxRepository extends AbstractRepository implements TaxRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Tax::class);
    }
}
