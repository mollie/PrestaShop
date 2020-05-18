<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

class OrderCollection extends \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\CursorCollection
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
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\Order($this->client);
    }
}
