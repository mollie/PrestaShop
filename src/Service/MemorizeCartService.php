<?php

namespace Mollie\Service;

use Order;

/**
 * Memorizes the cart
 */
class MemorizeCartService
{
    private $orderCartAssociationService;

    public function __construct(OrderCartAssociationService $orderCartAssociationService)
    {
        $this->orderCartAssociationService = $orderCartAssociationService;
    }

    public function memorizeCart(Order $toBeProcessedOrder)
    {
        // create a pending cart so we can repeat the process once again
        $this->orderCartAssociationService->createPendingCart($toBeProcessedOrder);
    }
}