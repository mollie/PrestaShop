<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Endpoints;

use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Customer;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\ResourceFactory;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Subscription;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\SubscriptionCollection;
class SubscriptionEndpoint extends \_PhpScoper5eddef0da618a\Mollie\Api\Endpoints\CollectionEndpointAbstract
{
    protected $resourcePath = "customers_subscriptions";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Subscription
     */
    protected function getResourceObject()
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Subscription($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param \stdClass $_links
     *
     * @return SubscriptionCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\SubscriptionCollection($this->client, $count, $_links);
    }
    /**
     * Create a subscription for a Customer
     *
     * @param Customer $customer
     * @param array $options
     * @param array $filters
     *
     * @return Subscription
     */
    public function createFor(\_PhpScoper5eddef0da618a\Mollie\Api\Resources\Customer $customer, array $options = [], array $filters = [])
    {
        $this->parentId = $customer->id;
        return parent::rest_create($options, $filters);
    }
    /**
     * @param Customer $customer
     * @param string $subscriptionId
     * @param array $parameters
     *
     * @return Subscription
     */
    public function getFor(\_PhpScoper5eddef0da618a\Mollie\Api\Resources\Customer $customer, $subscriptionId, array $parameters = [])
    {
        $this->parentId = $customer->id;
        return parent::rest_read($subscriptionId, $parameters);
    }
    /**
     * @param Customer $customer
     * @param string $from The first resource ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return SubscriptionCollection
     */
    public function listFor(\_PhpScoper5eddef0da618a\Mollie\Api\Resources\Customer $customer, $from = null, $limit = null, array $parameters = [])
    {
        $this->parentId = $customer->id;
        return parent::rest_list($from, $limit, $parameters);
    }
    /**
     * @param Customer $customer
     * @param string $subscriptionId
     *
     * @param array $data
     * @return null
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function cancelFor(\_PhpScoper5eddef0da618a\Mollie\Api\Resources\Customer $customer, $subscriptionId, array $data = [])
    {
        $this->parentId = $customer->id;
        return parent::rest_delete($subscriptionId, $data);
    }
    /**
     * Retrieves a collection of Subscriptions from Mollie.
     *
     * @param string $from The first payment ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return SubscriptionCollection
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function page($from = null, $limit = null, array $parameters = [])
    {
        $filters = \array_merge(["from" => $from, "limit" => $limit], $parameters);
        $apiPath = 'subscriptions' . $this->buildQueryString($filters);
        $result = $this->client->performHttpCall(self::REST_LIST, $apiPath);
        /** @var SubscriptionCollection $collection */
        $collection = $this->getResourceCollectionObject($result->count, $result->_links);
        foreach ($result->_embedded->{$collection->getCollectionResourceName()} as $dataResult) {
            $collection[] = \_PhpScoper5eddef0da618a\Mollie\Api\Resources\ResourceFactory::createFromApiResult($dataResult, $this->getResourceObject());
        }
        return $collection;
    }
}
