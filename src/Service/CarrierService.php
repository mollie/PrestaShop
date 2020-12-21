<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Service;

use Carrier;
use Configuration;
use Context;
use Mollie\Config\Config;

class CarrierService
{
	/**
	 * todo: fix this function
	 *
	 * @param array $trackingUrls
	 *
	 * @return array
	 */
	public function carrierConfig($trackingUrls)
	{
		if (!is_array($trackingUrls)) {
			$trackingUrls = [];
		}

		$carriers = Carrier::getCarriers(
			Context::getContext()->language->id,
			false,
			false,
			false,
			null,
			Carrier::ALL_CARRIERS
		);

		$configCarriers = [];
		foreach ($carriers as $carrier) {
			$idCarrier = (int) $carrier['id_carrier'];
			$configCarriers[] = [
				'id_carrier' => $idCarrier,
				'name' => $carrier['name'],
				'source' => ($carrier['external_module_name'] ? Config::MOLLIE_CARRIER_MODULE : Config::MOLLIE_CARRIER_CARRIER),
				'module' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
				'module_name' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
				'custom_url' => '',
			];
		}
		if (count($trackingUrls) !== count($configCarriers)) {
			Configuration::updateValue(Config::MOLLIE_TRACKING_URLS, json_encode($configCarriers));
		}

		return $configCarriers;
	}
}
