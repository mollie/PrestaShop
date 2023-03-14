<?php

declare(strict_types=1);

namespace Mollie\Adapter;

use Cart;
use Context;

class CartAdapter
{
    public function getCart(): Cart
    {
        return Context::getContext()->cart;
    }

    public function getProducts(): array
    {
        return Context::getContext()->cart->getProducts();
    }
}
