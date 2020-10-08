<?php

namespace MolliePrefix\Mollie\Api\Resources;

class InvoiceCollection extends \MolliePrefix\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "invoices";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \MolliePrefix\Mollie\Api\Resources\Invoice($this->client);
    }
}
