<?php

namespace Mollie\Repository;

interface CarrierRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getCarriersForOrder(int $id_zone, array $groups = null, \Cart $cart = null, &$error = []): array;
}
