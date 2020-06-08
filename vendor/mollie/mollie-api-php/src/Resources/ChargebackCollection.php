<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Resources;

class ChargebackCollection extends \_PhpScoper5eddef0da618a\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "chargebacks";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Chargeback($this->client);
    }
}
