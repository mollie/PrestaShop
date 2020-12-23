<?php

namespace Mollie\Provider\Shipment;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Utility\Decoder\DecoderInterface;

class AutomaticShipmentSenderStatusesProvider implements AutomaticShipmentSenderStatusesProviderInterface
{
	/**
	 * @var ConfigurationAdapter
	 */
	private $configurationAdapter;

	/**
	 * @var DecoderInterface
	 */
	private $decoder;

	public function __construct(
		ConfigurationAdapter $configurationAdapter,
		DecoderInterface $decoder
	) {
		$this->configurationAdapter = $configurationAdapter;
		$this->decoder = $decoder;
	}

	/**
	 * @return array
	 */
	public function getAutomaticShipmentSenderStatuses()
	{
		$autoShipStatuses = $this->configurationAdapter->get(Config::MOLLIE_AUTO_SHIP_STATUSES);

		if (empty($autoShipStatuses)) {
			return [];
		}

		return $this->decoder->decode($autoShipStatuses) ?: [];
	}
}
