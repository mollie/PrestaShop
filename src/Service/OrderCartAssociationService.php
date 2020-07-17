<?php

namespace Mollie\Service;

use MolPendingOrderCart;
use Order;

class OrderCartAssociationService
{
    private $cartDuplication;

    public function __construct(CartDuplicationService $cartDuplication)
    {
        $this->cartDuplication = $cartDuplication;
    }

    /**
     * @param Order $order
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createPendingCart(Order $order)
    {
        // globally restores the cart.
        $newCartId = $this->cartDuplication->restoreCart($order->id_cart);

        $pendingOrderCart = new MolPendingOrderCart();
        $pendingOrderCart->cart_id = $newCartId;
        $pendingOrderCart->order_id = $order->id;
        $pendingOrderCart->should_cancel_order = false;

        return $pendingOrderCart->add();
    }
}