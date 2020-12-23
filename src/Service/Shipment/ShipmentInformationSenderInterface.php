<?php

namespace Mollie\Service\Shipment;

use MolliePrefix\Mollie\Api\MollieApiClient;
use Order;

interface ShipmentInformationSenderInterface
{
	/**
	 * @param MollieApiClient|null $apiGateway
	 * @param Order $order
	 */
	public function sendShipmentInformation($apiGateway, Order $order);
}
