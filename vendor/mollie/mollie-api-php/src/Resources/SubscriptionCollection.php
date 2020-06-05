<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

class SubscriptionCollection extends \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\CursorCollection
{
    /**
     * @return string
     */
    public function getCollectionResourceName()
    {
        return "subscriptions";
    }
    /**
     * @return BaseResource
     */
    protected function createResourceObject()
    {
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\Subscription($this->client);
    }
}
