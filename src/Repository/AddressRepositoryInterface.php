<?php

namespace Mollie\Repository;

interface AddressRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getZoneById(int $id_address_delivery): int;
}
