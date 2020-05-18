<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

class SubscriptionCollection extends CursorCollection
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
        return new Subscription($this->client);
    }
}
