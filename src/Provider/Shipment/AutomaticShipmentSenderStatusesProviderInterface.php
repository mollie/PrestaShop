<?php

namespace Mollie\Provider\OrderState;

interface AutomaticShipmentSenderStatusesProviderInterface
{
	/**
	 * @return array
	 */
	public function provideAutomaticShipmentSenderStatuses();
}
