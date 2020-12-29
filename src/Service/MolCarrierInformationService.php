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
use Context;
use MolCarrierInformation;
use Mollie\Config\Config;
use Mollie\Repository\MolCarrierInformationRepository;

class MolCarrierInformationService
{
	/**
	 * @var MolCarrierInformationRepository
	 */
	private $informationRepository;

	public function __construct(MolCarrierInformationRepository $informationRepository)
	{
		$this->informationRepository = $informationRepository;
	}

	public function getAllCarriersInformation($langId = null)
	{
		if (!$langId) {
			$langId = Context::getContext()->language->id;
		}

		$carriers = Carrier::getCarriers(
			$langId,
			false,
			false,
			false,
			null,
			Carrier::ALL_CARRIERS
		);

		$configCarriers = [];
		/** @var Carrier $carrier */
		foreach ($carriers as $carrier) {
			$carrier = new Carrier($carrier['id_carrier']);
			$carrierInfoId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($carrier->id);
			if ($carrierInfoId) {
				$carrierInfo = new MolCarrierInformation($carrierInfoId);
				$configCarriers[] = [
					'id_carrier' => $carrier->id,
					'name' => $carrier->name,
					'source' => $carrierInfo->url_source,
					'module' => $carrier->external_module_name,
					'module_name' => $carrier->external_module_name,
					'custom_url' => $carrierInfo->custom_url,
				];
			} else {
				$configCarriers[] = [
					'id_carrier' => $carrier->id,
					'name' => $carrier->name,
					'source' => $carrier->external_module_name ? Config::MOLLIE_CARRIER_MODULE : Config::MOLLIE_CARRIER_CARRIER,
					'module' => $carrier->external_module_name,
					'module_name' => $carrier->external_module_name,
					'custom_url' => '',
				];
			}
		}

		return $configCarriers;
	}

	public function saveMolCarrierInfo($carrierId, $urlSource, $customUrl = null)
	{
		$carrierInformationId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($carrierId);
		$carrierInformation = new MolCarrierInformation($carrierInformationId);
		$carrierInformation->id_carrier = $carrierId;
		$carrierInformation->url_source = $urlSource;
		$carrierInformation->custom_url = $customUrl;

		$carrierInformation->save();
	}
}
