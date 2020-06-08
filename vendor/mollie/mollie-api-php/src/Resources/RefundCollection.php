<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Resources;

class RefundCollection extends \_PhpScoper5eddef0da618a\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "refunds";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Refund($this->client);
    }
}
