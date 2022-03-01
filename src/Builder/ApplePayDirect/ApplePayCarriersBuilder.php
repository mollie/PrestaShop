<?php

namespace Mollie\Builder\ApplePayDirect;

use Mollie\DTO\ApplePay\Carrier\Carrier;

class ApplePayCarriersBuilder
{
    /**
     * @param array $carriers
     * @return Carrier[]
     */
    public function build(array $carriers): array
    {
        $applePayCarriers = [];
        foreach ($carriers as $carrier) {
//            $priceRange = Carrier::getDeliveryPriceByRanges($carrier->getRangeTable(), (int) $carrier->id);
            $applePayCarriers[] = new Carrier(
                $carrier['name'],
                $carrier['delay'],
                $carrier['id_carrier'],
                0
            );
        }

        return $applePayCarriers;
    }
}
