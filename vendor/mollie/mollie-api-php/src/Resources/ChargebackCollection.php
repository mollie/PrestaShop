<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

class ChargebackCollection extends \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\CursorCollection
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
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\Chargeback($this->client);
    }
}
