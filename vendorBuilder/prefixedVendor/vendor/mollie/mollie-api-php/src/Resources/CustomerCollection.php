<?php

namespace MolliePrefix\Mollie\Api\Resources;

class CustomerCollection extends \MolliePrefix\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "customers";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \MolliePrefix\Mollie\Api\Resources\Customer($this->client);
    }
}
