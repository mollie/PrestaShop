<?php

namespace Mollie\Repository;

use Cart;

class CartRepository extends AbstractRepository implements CartRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Cart::class);
    }
}
