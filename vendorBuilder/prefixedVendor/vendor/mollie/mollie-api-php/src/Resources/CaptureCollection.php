<?php

namespace MolliePrefix\Mollie\Api\Resources;

class CaptureCollection extends \MolliePrefix\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "captures";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \MolliePrefix\Mollie\Api\Resources\Capture($this->client);
    }
}
