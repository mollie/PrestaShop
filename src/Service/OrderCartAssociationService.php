<?php

namespace Mollie\Service;

use Order;

class OrderCartAssociationService
{
    private $cartDuplication;

    public function __construct(CartDuplicationService $cartDuplication)
    {
        $this->cartDuplication = $cartDuplication;
    }

    public function associateOrderToPendingCart(Order $order)
    {
        // globally restores the cart.
        $newCartId = $this->cartDuplication->restoreCart($order->id_cart);

    }
}