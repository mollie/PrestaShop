<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Refund;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\RefundCollection;
class RefundEndpoint extends \_PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\CollectionEndpointAbstract
{
    protected $resourcePath = "refunds";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Refund
     */
    protected function getResourceObject()
    {
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\Refund($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param \stdClass $_links
     *
     * @return RefundCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\RefundCollection($this->client, $count, $_links);
    }
    /**
     * Retrieves a collection of Refunds from Mollie.
     *
     * @param string $from The first refund ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return RefundCollection
     * @throws ApiException
     */
    public function page($from = null, $limit = null, array $parameters = [])
    {
        return $this->rest_list($from, $limit, $parameters);
    }
}
