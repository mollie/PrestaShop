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

use Address;
use Carrier;
use Context;
use Country;
use Language;
use MolCarrierInformation;
use Mollie\Config\Config;
use Mollie\Handler\ErrorHandler\ErrorHandler;
use Mollie\Repository\MolCarrierInformationRepository;
use Mollie\Repository\OrderShipmentRepository;
use Order;
use OrderCarrier;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Validate;

class ShipmentService implements ShipmentServiceInterface
{
	/**
	 * @var OrderShipmentRepository
	 */
	private $orderShipmentRepository;

	/**
	 * @var MolCarrierInformationRepository
	 */
	private $informationRepository;

	public function __construct(
		OrderShipmentRepository $orderShipmentRepository,
		MolCarrierInformationRepository $informationRepository
	) {
		$this->orderShipmentRepository = $orderShipmentRepository;
		$this->informationRepository = $informationRepository;
	}

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
	public function getShipmentInformation($orderReference)
	{
		$orders = Order::getByReference($orderReference);
		/** @var Order $order */
		$order = $orders->getFirst();
		if (!Validate::isLoadedObject($order)) {
			return null;
		}
		$invoiceAddress = new Address($order->id_address_invoice);
		$deliveryAddress = new Address($order->id_address_delivery);
		$carrierInformationId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($order->id_carrier);
		$carrierInformation = new MolCarrierInformation($carrierInformationId);
		if (!Validate::isLoadedObject($invoiceAddress)
			|| !Validate::isLoadedObject($deliveryAddress)
			|| !Validate::isLoadedObject($carrierInformation)
		) {
			return [];
		}

		if (Config::MOLLIE_CARRIER_NO_TRACKING_INFO === $carrierInformation->url_source) {
			return [];
		}

		$langId = Context::getContext()->language->id;
		if (Config::MOLLIE_CARRIER_MODULE === $carrierInformation->url_source) {
			$carrier = new Carrier($order->id_carrier);
			if (in_array($carrier->external_module_name, ['postnl', 'myparcel'])) {
				$table = 'postnl' === $carrier->external_module_name ? 'postnlmod_order' : 'myparcel_order';

				try {
					$info = $this->orderShipmentRepository->getShipmentInformation($table, $order->id);
					if ($info['tracktrace'] && $info['postcode']) {
						$postcode = Tools::strtoupper(str_replace(' ', '', $info['postcode']));
						$langIso = Tools::strtoupper(Language::getIsoById($langId));
						$countryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));
						$tracktrace = $info['tracktrace'];

						return [
							'tracking' => [
								'carrier' => 'PostNL',
								'code' => $info['tracktrace'],
								'url' => "http://postnl.nl/tracktrace/?L={$langIso}&B={$tracktrace}&P={$postcode}&D={$countryIso}&T=C",
							],
						];
					}
				} catch (PrestaShopDatabaseException $e) {
					$errorHandler = ErrorHandler::getInstance();
					$errorHandler->handle($e, $e->getCode(), false);

					return [];
				}
			}

			return [];
		}

		if (Config::MOLLIE_CARRIER_CARRIER === $carrierInformation->url_source) {
			$carrier = new Carrier($order->id_carrier);
			$shippingNumber = $order->shipping_number;
			if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
				$orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
				$shippingNumber = $orderCarrier->tracking_number;
			}

			if (!$shippingNumber || !$carrier->name) {
				return [];
			}

			return [
				'tracking' => [
					'carrier' => $carrier->name,
					'code' => $shippingNumber,
					'url' => str_replace('@', $shippingNumber, $carrier->url),
				],
			];
		}

		if (Config::MOLLIE_CARRIER_CUSTOM === $carrierInformation->url_source) {
			$carrier = new Carrier($order->id_carrier);
			$shippingNumber = $order->shipping_number;
			if (!$shippingNumber && method_exists($order, 'getIdOrderCarrier')) {
				$orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
				$shippingNumber = $orderCarrier->tracking_number;
			}

			$invoicePostcode = Tools::strtoupper(str_replace(' ', '', $invoiceAddress->postcode));
			$invoiceCountryIso = Tools::strtoupper(Country::getIsoById($invoiceAddress->id_country));
			$deliveryPostcode = Tools::strtoupper(str_replace(' ', '', $deliveryAddress->postcode));
			$deliveryCountryIso = Tools::strtoupper(Country::getIsoById($deliveryAddress->id_country));

			$langIso = Tools::strtoupper(Language::getIsoById($langId));

			if (!$shippingNumber || !$carrier->name) {
				return [];
			}

			$info = [
				'@' => $shippingNumber,
				'%%shipping_number%%' => $shippingNumber,
				'%%invoice.country_iso%%' => $invoiceCountryIso,
				'%%invoice.postcode%%' => $invoicePostcode,
				'%%delivery.country_iso%%' => $deliveryCountryIso,
				'%%delivery.postcode%%' => $deliveryPostcode,
				'%%lang_iso%%' => $langIso,
			];

			return [
				'tracking' => [
					'carrier' => $carrier->name,
					'code' => $shippingNumber,
					'url' => str_ireplace(
						array_keys($info),
						array_values($info),
						$carrierInformation->custom_url
					),
				],
			];
		}

		return [];
	}
}
