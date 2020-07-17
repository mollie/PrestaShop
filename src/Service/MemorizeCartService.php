<?php

namespace Mollie\Service;

use Order;

/**
 * Memorizes the cart
 */
class MemorizeCartService
{
    private $rejectPendingOrderService;
    private $orderCartAssociationService;
    private $cancelPendingOrderService;

    public function __construct(
        RejectPendingOrderService $rejectPendingOrderService,
        OrderCartAssociationService $orderCartAssociationService,
        CancelPendingOrderService $cancelPendingOrderService
    ) {
        $this->rejectPendingOrderService = $rejectPendingOrderService;
        $this->orderCartAssociationService = $orderCartAssociationService;
        $this->cancelPendingOrderService = $cancelPendingOrderService;
    }

    public function memorizeCart($transactionId, Order $toBeProcessedOrder)
    {
        // lets reject previous order if such exist - at this point user returned to the cart and started new order
        $this->rejectPendingOrderService->markAsRejectedPossiblePendingOrder($transactionId);

        // todo: why cancel does not work ?
        // $this->cancelPendingOrderService->cancel();

        // create a pending cart so we can repeat the process once again
        $this->orderCartAssociationService->createPendingCart($toBeProcessedOrder);
    }
}