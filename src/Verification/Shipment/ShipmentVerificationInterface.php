<?php

namespace Mollie\Verification\Shipment;

use Mollie\Exception\ShipmentCannotBeSentException;
use Order;
use OrderState;

interface ShipmentVerificationInterface
{
	/**
	 * @param Order $order
	 * @param OrderState $orderState
	 *
	 * @throws ShipmentCannotBeSentException
	 */
	public function verify(Order $order, OrderState $orderState);
}
