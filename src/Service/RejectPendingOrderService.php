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

    public function rejectPossiblePendingOrder()
    {
        $order = $this->getOrder();

        if (!$order) {
            return;
        }

        $pendingStatusId = (int) Configuration::get(Config::STATUS_MOLLIE_AWAITING);

        $isPendingOrder = (int) $order->getCurrentState() === $pendingStatusId;

        if (!$isPendingOrder) {
            return;
        }

        $psCancelledStatusId = Configuration::get(Config::MOLLIE_STATUS_CANCELED);

        $order->setCurrentState($psCancelledStatusId);
    }

    /**
     * @return Order|null
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getOrder()
    {
        $globalCartId = \Context::getContext()->cart->id;

        /** @var null|MolPendingOrderCart $pendingOrderCart */
        $pendingOrderCart = $this->repo->findOneBy([
            'cart_id' => (int) $globalCartId,
        ]);

        if (!$pendingOrderCart) {
            return null;
        }

        return new Order($pendingOrderCart->order_id);
    }
}