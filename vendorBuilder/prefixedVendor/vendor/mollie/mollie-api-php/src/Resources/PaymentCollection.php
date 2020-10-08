<?php

namespace MolliePrefix\Mollie\Api\Resources;

class PaymentCollection extends \MolliePrefix\Mollie\Api\Resources\CursorCollection
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
        return new \MolliePrefix\Mollie\Api\Resources\Payment($this->client);
    }
}
