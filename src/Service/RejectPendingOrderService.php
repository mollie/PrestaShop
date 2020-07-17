<?php

namespace Mollie\Service;

use Configuration;
use Mollie\Config\Config;
use Mollie\Repository\PendingOrderCartRepository;
use MolPendingOrderCart;
use Order;

/**
 * Rejects pending order on prestashop side.
 */
class RejectPendingOrderService
{
    private $repo;

    public function __construct(PendingOrderCartRepository $repo)
    {
        $this->repo = $repo;
    }

    public function markAsRejectedPossiblePendingOrder()
    {
        $globalCartId = \Context::getContext()->cart->id;

        /** @var null|MolPendingOrderCart $pendingOrderCart */
        $pendingOrderCart = $this->repo->findOneBy([
            'cart_id' => (int) $globalCartId,
        ]);

        $order = new Order($pendingOrderCart ? $pendingOrderCart->order_id : 0);

        if (!$order) {
            return;
        }

        $pendingStatusId = (int) Configuration::get(Config::STATUS_MOLLIE_AWAITING);

        $isPendingOrder = (int) $order->getCurrentState() === $pendingStatusId;

        $pendingOrderCart->should_cancel_order = $isPendingOrder;
        $pendingOrderCart->update();
    }
}