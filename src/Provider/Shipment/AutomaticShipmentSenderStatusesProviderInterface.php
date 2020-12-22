<?php

namespace Mollie\Provider\Shipment;

interface AutomaticShipmentSenderStatusesProviderInterface
{
	/**
	 * @return array
	 */
	public function getAutomaticShipmentSenderStatuses();
}
