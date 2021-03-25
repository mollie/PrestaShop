<?php

namespace Mollie\Handler\Shipment;

use Mollie\Api\MollieApiClient;
use Order;
use OrderState;

interface ShipmentSenderHandlerInterface
{
	/**
	 * @param MollieApiClient $apiClient
	 * @param Order $order
	 * @param OrderState $orderState
	 */
	public function handleShipmentSender(MollieApiClient $apiClient, Order $order, OrderState $orderState);
}
