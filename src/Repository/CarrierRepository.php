<?php

namespace Mollie\Repository;

class CarrierRepository extends AbstractRepository implements CarrierRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\Carrier::class);
    }

    public function getCarriersForOrder(int $id_zone, array $groups = null, \Cart $cart = null, &$error = []): array
    {
        return \Carrier::getCarriersForOrder($id_zone, $groups, $cart, $error);
    }
}
