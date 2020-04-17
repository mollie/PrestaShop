<?php

namespace Mollie\Service;

use Carrier;
use Configuration;
use Context;
use Mollie\Config\Config;
use Order;
use Validate;

class CarrierService
{
    /**
     * Get carrier configuration
     *
     * @return array
     *
     * @since 3.3.0
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
            $idCarrier = (int)$carrier['id_carrier'];
            $configCarriers[] = [
                'id_carrier' => $idCarrier,
                'name' => $carrier['name'],
                'source' => isset($dbConfig[$idCarrier]) ? $trackingUrls[$idCarrier]['source'] : ($carrier['external_module_name'] ? Config::MOLLIE_CARRIER_MODULE : Config::MOLLIE_CARRIER_CARRIER),
                'module' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
                'module_name' => !empty($carrier['external_module_name']) ? $carrier['external_module_name'] : null,
                'custom_url' => isset($dbConfig[$idCarrier]) ? $trackingUrls[$idCarrier]['custom_url'] : '',
            ];
        }
        if (count($trackingUrls) !== count($configCarriers)) {
            Configuration::updateValue(Config::MOLLIE_TRACKING_URLS, json_encode($configCarriers));
        }

        return $configCarriers;
    }

    /**
     * Get carrier config for order
     *
     * @param Order|int $order
     *
     * @return array|null Configuration or `null` if not tracking
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderCarrierConfig($order)
    {
        if (is_int($order)) {
            $order = new Order($order);
        }

        if (!$carrierConfig = @json_decode(Configuration::get(Config::MOLLIE_TRACKING_URLS), true)) {
            return null;
        }

        if (!Validate::isLoadedObject($order) || !$order->id_carrier) {
            return null;
        }

        if (!isset($carrierConfig[$order->id_carrier]) || !isset($carrierConfig[$order->id_carrier]['source'])) {
            return null;
        }

        return $carrierConfig[$order->id_carrier];
    }
}