<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Resources;

class PaymentCollection extends \_PhpScoper5eddef0da618a\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "payments";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Payment($this->client);
    }
}
