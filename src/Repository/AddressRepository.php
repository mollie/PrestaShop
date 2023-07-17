<?php

namespace Mollie\Repository;

use Address;

class AddressRepository extends AbstractRepository implements AddressRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Address::class);
    }
}
