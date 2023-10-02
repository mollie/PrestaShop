<?php

namespace Mollie\Repository;

class AddressRepository extends AbstractRepository implements AddressRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Address::class);
    }

    public function getZoneById(int $id_address_delivery): int
    {
        return \Address::getZoneById($id_address_delivery);
    }
}
