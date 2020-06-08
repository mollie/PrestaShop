<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Resources;

class OrderCollection extends \_PhpScoper5eddef0da618a\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "orders";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Order($this->client);
    }
}
