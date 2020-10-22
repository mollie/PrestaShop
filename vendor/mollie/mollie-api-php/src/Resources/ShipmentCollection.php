<?php

namespace MolliePrefix\Mollie\Api\Resources;

class ShipmentCollection extends \MolliePrefix\Mollie\Api\Resources\BaseCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return 'shipments';
    }
}
