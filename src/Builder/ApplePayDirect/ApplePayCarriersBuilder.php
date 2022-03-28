<?php

namespace Mollie\Builder\ApplePayDirect;

use Carrier;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;

class ApplePayCarriersBuilder
{
    /**
     * @param array $carriers
     *
     * @return AppleCarrier[]
     */
    public function build(array $carriers, int $idZone): array
    {
        $price = 0;
        $applePayCarriers = [];
        foreach ($carriers as $carrier) {
            $carrierObj = new Carrier($carrier['id_carrier']);
            if ($carrierObj->getRangeTable()) {
                $priceRanges = Carrier::getDeliveryPriceByRanges($carrierObj->getRangeTable(), (int) $carrier['id_carrier']);
                foreach ($priceRanges as $priceRange) {
                    if ((int) $priceRange['id_zone'] === $idZone) {
                        $price = (float) $priceRange['price'];
                    }
                }
            }
            $applePayCarriers[] = new AppleCarrier(
                $carrier['name'],
                $carrier['delay'],
                $carrier['id_carrier'],
                $price
            );
        }

        return $applePayCarriers;
    }
}
