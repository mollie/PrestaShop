<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Endpoints;

use _PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\Chargeback;
use _PhpScoper5eddef0da618a\Mollie\Api\Resources\ChargebackCollection;
class ChargebackEndpoint extends \_PhpScoper5eddef0da618a\Mollie\Api\Endpoints\CollectionEndpointAbstract
{
    protected $resourcePath = "chargebacks";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Chargeback
     */
    protected function getResourceObject()
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Chargeback($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param \stdClass $_links
     *
     * @return ChargebackCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \_PhpScoper5eddef0da618a\Mollie\Api\Resources\ChargebackCollection($this->client, $count, $_links);
    }
    /**
     * Retrieves a collection of Chargebacks from Mollie.
     *
     * @param string $from The first chargeback ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return ChargebackCollection
     * @throws ApiException
     */
    public function page($from = null, $limit = null, array $parameters = [])
    {
        return $this->rest_list($from, $limit, $parameters);
    }
}
