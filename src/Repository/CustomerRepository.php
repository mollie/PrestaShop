<?php

namespace Mollie\Repository;

class CustomerRepository extends AbstractRepository implements CustomerRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Customer::class);
    }
}
