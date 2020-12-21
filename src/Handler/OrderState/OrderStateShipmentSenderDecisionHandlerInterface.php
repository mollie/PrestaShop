<?php

namespace Mollie\Handler\OrderState;

use Order;
use OrderState;

interface OrderStateShipmentSenderDecisionHandlerInterface
{
	/**
	 * Returns if order shipment is allowed to be sent on order state change
	 *
	 * @param Order $order
	 * @param OrderState $orderState
	 *
	 * @return bool
	 */
	public function canShipmentDataBeSent(Order $order, OrderState $orderState);
}
