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
        /* @phpstan-ignore-next-line */
        return Context::getContext()->cart ? Context::getContext()->cart->getProducts() : [];
    }
}
