<?php

namespace Mollie\Service;

use Address;
use Carrier;
use Context;
use Country;
use Language;
use MolCarrierInformation;
use Mollie\Config\Config;
use Mollie\Repository\MolCarrierInformationRepository;
use Mollie\Repository\OrderShipmentRepository;
use Order;
use OrderCarrier;
use PrestaShopDatabaseException;
use PrestaShopException;
use Tools;
use Validate;

class ShipmentService
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
     * Get shipment information
     *
     * @param int $idOrder
     *
     * @return array|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 3.3.0
     */
    public function getShipmentInformation($orderReference)
    {
        $order = Order::getByReference($orderReference)[0];
        if (!Validate::isLoadedObject($order)) {
            return null;
        }
        $invoiceAddress = new Address($order->id_address_invoice);
        $deliveryAddress = new Address($order->id_address_delivery);
        $carrierInformationId = $this->informationRepository->getMollieCarrierInformationIdByCarrierId($order->id_carrier);
        $carrierInformation = new MolCarrierInformation($carrierInformationId);
        if (!Validate::isLoadedObject($invoiceAddress)
            || !Validate::isLoadedObject($deliveryAddress)
            || !$carrierInformation
        ) {
            return [];
        }

        if ($carrierInformation === Config::MOLLIE_CARRIER_NO_TRACKING_INFO) {
            return [];
        }

        $langId = Context::getContext()->language->id;
        if ($carrierInformation->url_source === Config::MOLLIE_CARRIER_MODULE) {
            $carrier = new Carrier($order->id_carrier);
            if (in_array($carrier->external_module_name, ['postnl', 'myparcel'])) {
                $table = $carrier->external_module_name === 'postnl' ? 'postnlmod_order' : 'myparcel_order';

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
                    return [];
                }
            }

            return [];
        }

        if ($carrierInformation->url_source === Config::MOLLIE_CARRIER_CARRIER) {
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

        if ($carrierInformation->url_source === Config::MOLLIE_CARRIER_CUSTOM) {
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
                        $carrierInformation->url_source
                    ),
                ],
            ];
        }

        return [];
    }

}