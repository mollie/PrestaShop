<?php

namespace _PhpScoper5ece82d7231e4\Mollie\Api\Endpoints;

use _PhpScoper5ece82d7231e4\Mollie\Api\Resources\BaseCollection;
abstract class CollectionEndpointAbstract extends \_PhpScoper5ece82d7231e4\Mollie\Api\Endpoints\EndpointAbstract
{
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param \stdClass $_links
     *
     * @return BaseCollection
     */
    protected abstract function getResourceCollectionObject($count, $_links);
}
