<?php

namespace Mollie\Service;

use PrestaShopDatabaseException;
use PrestaShopException;

interface ShipmentServiceInterface
{
	/**
	 * Get shipment information.
	 *
	 * @param string $orderReference
	 *
	 * @return array|null
	 *
	 * @throws PrestaShopDatabaseException
	 * @throws PrestaShopException
	 *
	 * @since 3.3.0
	 */
	public function getShipmentInformation($orderReference);
}
