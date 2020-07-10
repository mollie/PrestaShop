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

    public function __construct(
        RejectPendingOrderService $rejectPendingOrderService,
        OrderCartAssociationService $orderCartAssociationService
    ) {
        $this->rejectPendingOrderService = $rejectPendingOrderService;
        $this->orderCartAssociationService = $orderCartAssociationService;
    }

    public function memorizeCart(Order $toBeProcessedOrder)
    {
        // lets reject previous order if such exist - at this point user returned to the cart and started new order
        $this->rejectPendingOrderService->rejectPossiblePendingOrder();

        // create a pending cart so we can repeat the process once again
        $this->orderCartAssociationService->createPendingCart($toBeProcessedOrder);
    }
}