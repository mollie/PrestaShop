<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Method;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\MethodCollection;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\ResourceFactory;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\BaseResource;
use stdClass;

class MethodEndpoint extends CollectionEndpointAbstract
{
    protected $resourcePath = "methods";
    /**
     * @return Method
     */
    protected function getResourceObject()
    {
        return new Method($this->client);
    }
    /**
     * Retrieve all active methods. In test mode, this includes pending methods. The results are not paginated.
     *
     * @deprecated Use allActive() instead
     * @param array $parameters
     *
     * @return BaseCollection|\Mollie\Api\Resources\MethodCollection
     * @throws ApiException
     */
    public function all(array $parameters = [])
    {
        return $this->allActive($parameters);
    }
    /**
     * Retrieve all active methods for the organization. In test mode, this includes pending methods.
     * The results are not paginated.
     *
     * @param array $parameters
     *
     * @return BaseCollection|\Mollie\Api\Resources\MethodCollection
     * @throws ApiException
     */
    public function allActive(array $parameters = [])
    {
        return parent::rest_list(null, null, $parameters);
    }
    /**
     * Retrieve all available methods for the organization, including activated and not yet activated methods. The
     * results are not paginated. Make sure to include the profileId parameter if using an OAuth Access Token.
     *
     * @param array $parameters Query string parameters.
     * @return BaseCollection|\Mollie\Api\Resources\MethodCollection
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function allAvailable(array $parameters = [])
    {
        $url = 'methods/all' . $this->buildQueryString($parameters);
        $result = $this->client->performHttpCall('GET', $url);
        return ResourceFactory::createBaseResourceCollection($this->client, Method::class, $result->_embedded->methods, $result->_links);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param stdClass $_links
     *
     * @return MethodCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new MethodCollection($count, $_links);
    }
    /**
     * Retrieve a payment method from Mollie.
     *
     * Will throw a ApiException if the method id is invalid or the resource cannot be found.
     *
     * @param string $methodId
     * @param array $parameters
     * @return BaseResource|\Mollie\Api\Resources\Method
     * @throws ApiException
     */
    public function get($methodId, array $parameters = [])
    {
        if (empty($methodId)) {
            throw new ApiException("Method ID is empty.");
        }
        return parent::rest_read($methodId, $parameters);
    }
}
