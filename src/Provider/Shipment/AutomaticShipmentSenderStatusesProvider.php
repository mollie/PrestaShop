<?php

namespace Mollie\Provider\OrderState;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;

class AutomaticShipmentSenderStatusesProvider implements AutomaticShipmentSenderStatusesProviderInterface
{
	/**
	 * @var ConfigurationAdapter
	 */
	private $configurationAdapter;

	public function __construct(ConfigurationAdapter $configurationAdapter)
	{
		$this->configurationAdapter = $configurationAdapter;
	}

	/**
	 * @return array
	 */
	public function provideAutomaticShipmentSenderStatuses()
	{
		$autoShipStatuses = $this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_STATUSES);
		$statuses = $this->decodeJson($autoShipStatuses);

		return $statuses ?: [];
	}

	/**
	 * @param string $autoShipStatuses
	 *
	 * @return array|null
	 */
	private function decodeJson($autoShipStatuses)
	{
		return json_decode($autoShipStatuses);
	}
}
