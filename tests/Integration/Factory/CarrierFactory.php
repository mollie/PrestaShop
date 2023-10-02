<?php

namespace Mollie\Tests\Integration\Factory;

class CarrierFactory implements FactoryInterface
{
    public static function create(array $data = [])
    {
        $carrier = new \Carrier(null, (int) \Configuration::get('PS_LANG_DEFAULT'));

        $carrier->name = $data['name'] ?? 'test-name';
        $carrier->active = $data['active'] ?? true;
        $carrier->delay = $data['delay'] ?? '28 days later';

        //NOTE to if true would add PS_SHIPPING_HANDLING from configuration to shipping price.
        $carrier->shipping_handling = $data['shipping_handling'] ?? false;

        //NOTE need to do it like this because otherwise it would not show up as option.
        if (isset($data['price']) && !empty((int) $data['price'])) {
            $carrier->shipping_method = \Carrier::SHIPPING_METHOD_PRICE;
        } else {
            $carrier->shipping_method = \Carrier::SHIPPING_METHOD_FREE;
        }

        $carrier->shipping_method = $data['shipping_method'] ?? $carrier->shipping_method;

        $carrier->save();

        $rangePrice = new \RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = 0;
        $rangePrice->delimiter2 = 1;

        $rangePrice->save();

        $zones = \Zone::getZones();
        $prices = [];

        foreach ($zones as $zone) {
            if (in_array($zone['id_zone'], $data['id_zones_to_delete'] ?? [], false)) {
                continue;
            }

            $carrier->addZone($zone['id_zone']);

            $prices[] = [
                'id_range_price' => $rangePrice->id,
                'id_range_weight' => null,
                'id_carrier' => (int) $carrier->id,
                'id_zone' => (int) $zone['id_zone'],
                'price' => $data['price'] ?? 0,
            ];
        }
        // enable all zones
        $carrier->addDeliveryPrice($prices);

        $allGroups = \Group::getGroups(\Context::getContext()->language->id);

        $carrier->setGroups(array_column($allGroups, 'id_group'));

        return $carrier;
    }
}
