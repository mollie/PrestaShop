<?php

namespace MolliePrefix\Mollie\Api\Resources;

class SettlementCollection extends \MolliePrefix\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "settlements";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \MolliePrefix\Mollie\Api\Resources\Settlement($this->client);
    }
}
