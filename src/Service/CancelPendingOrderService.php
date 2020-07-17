<?php

namespace Mollie\Service;

use Configuration;
use Mollie\Config\Config;
use Mollie\Exception\CancelPendingOrderException;
use Mollie\Repository\PendingOrderCartRepository;
use Order;

class CancelPendingOrderService
{
    private $cancelService;
    private $pendingOrderCartRepository;

    public function __construct(
        CancelService $cancelService,
        PendingOrderCartRepository $pendingOrderCartRepository
    ) {
        $this->cancelService = $cancelService;
        $this->pendingOrderCartRepository = $pendingOrderCartRepository;
    }

    /**
     * @param string $transactionId
     * @param Order $order
     *
     * @return bool
     *
     * @throws CancelPendingOrderException
     */
    public function cancelOrder($transactionId, Order $order)
    {
        if (!$this->pendingOrderCartRepository->isPendingCancellableOrder($order->id)) {
            return false;
        }

        $status = $this->cancelService->doCancelOrderLines($transactionId);

        if (!$status['success']) {
            throw new CancelPendingOrderException(
                "Order {$order->id} cant be cancelled due to previous error: {$status['detailed']}"
            );
        }

        return true;
    }
}