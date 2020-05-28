<?php

namespace _PhpScoper5ece82d7231e4\Mollie\Api\Resources;

class PaymentCollection extends \_PhpScoper5ece82d7231e4\Mollie\Api\Resources\CursorCollection
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
        return new \_PhpScoper5ece82d7231e4\Mollie\Api\Resources\Payment($this->client);
    }
}
