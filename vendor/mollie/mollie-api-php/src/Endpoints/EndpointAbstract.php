<?php

namespace MolliePrefix\Mollie\Api\Endpoints;

use MolliePrefix\Mollie\Api\Exceptions\ApiException;
use MolliePrefix\Mollie\Api\MollieApiClient;
use MolliePrefix\Mollie\Api\Resources\BaseCollection;
use MolliePrefix\Mollie\Api\Resources\BaseResource;
use MolliePrefix\Mollie\Api\Resources\ResourceFactory;
abstract class EndpointAbstract
{
    const REST_CREATE = \MolliePrefix\Mollie\Api\MollieApiClient::HTTP_POST;
    const REST_UPDATE = \MolliePrefix\Mollie\Api\MollieApiClient::HTTP_PATCH;
    const REST_READ = \MolliePrefix\Mollie\Api\MollieApiClient::HTTP_GET;
    const REST_LIST = \MolliePrefix\Mollie\Api\MollieApiClient::HTTP_GET;
    const REST_DELETE = \MolliePrefix\Mollie\Api\MollieApiClient::HTTP_DELETE;
    /**
     * @var MollieApiClient
     */
    protected $client;
    /**
     * @var string
     */
    protected $resourcePath;
    /**
     * @var string|null
     */
    protected $parentId;
    /**
     * @param MollieApiClient $api
     */
    public function __construct(\MolliePrefix\Mollie\Api\MollieApiClient $api)
    {
        $this->client = $api;
    }
    /**
     * @param array $filters
     * @return string
     */
    protected function buildQueryString(array $filters)
    {
        if (empty($filters)) {
            return "";
        }
        foreach ($filters as $key => $value) {
            if ($value === \true) {
                $filters[$key] = "true";
            }
            if ($value === \false) {
                $filters[$key] = "false";
            }
        }
        return "?" . \http_build_query($filters, "", "&");
    }
    /**
     * @param array $body
     * @param array $filters
     * @return BaseResource
     * @throws ApiException
     */
    protected function rest_create(array $body, array $filters)
    {
        $result = $this->client->performHttpCall(self::REST_CREATE, $this->getResourcePath() . $this->buildQueryString($filters), $this->parseRequestBody($body));
        return \MolliePrefix\Mollie\Api\Resources\ResourceFactory::createFromApiResult($result, $this->getResourceObject());
    }
    /**
     * Retrieves a single object from the REST API.
     *
     * @param string $id Id of the object to retrieve.
     * @param array $filters
     * @return BaseResource
     * @throws ApiException
     */
    protected function rest_read($id, array $filters)
    {
        if (empty($id)) {
            throw new \MolliePrefix\Mollie\Api\Exceptions\ApiException("Invalid resource id.");
        }
        $id = \urlencode($id);
        $result = $this->client->performHttpCall(self::REST_READ, "{$this->getResourcePath()}/{$id}" . $this->buildQueryString($filters));
        return \MolliePrefix\Mollie\Api\Resources\ResourceFactory::createFromApiResult($result, $this->getResourceObject());
    }
    /**
     * Sends a DELETE request to a single Molle API object.
     *
     * @param string $id
     * @param array $body
     *
     * @return BaseResource
     * @throws ApiException
     */
    protected function rest_delete($id, array $body = [])
    {
        if (empty($id)) {
            throw new \MolliePrefix\Mollie\Api\Exceptions\ApiException("Invalid resource id.");
        }
        $id = \urlencode($id);
        $result = $this->client->performHttpCall(self::REST_DELETE, "{$this->getResourcePath()}/{$id}", $this->parseRequestBody($body));
        if ($result === null) {
            return null;
        }
        return \MolliePrefix\Mollie\Api\Resources\ResourceFactory::createFromApiResult($result, $this->getResourceObject());
    }
    /**
     * Get a collection of objects from the REST API.
     *
     * @param string $from The first resource ID you want to include in your list.
     * @param int $limit
     * @param array $filters
     *
     * @return BaseCollection
     * @throws ApiException
     */
    protected function rest_list($from = null, $limit = null, array $filters = [])
    {
        $filters = \array_merge(["from" => $from, "limit" => $limit], $filters);
        $apiPath = $this->getResourcePath() . $this->buildQueryString($filters);
        $result = $this->client->performHttpCall(self::REST_LIST, $apiPath);
        /** @var BaseCollection $collection */
        $collection = $this->getResourceCollectionObject($result->count, $result->_links);
        foreach ($result->_embedded->{$collection->getCollectionResourceName()} as $dataResult) {
            $collection[] = \MolliePrefix\Mollie\Api\Resources\ResourceFactory::createFromApiResult($dataResult, $this->getResourceObject());
        }
        return $collection;
    }
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return BaseResource
     */
    protected abstract function getResourceObject();
    /**
     * @param string $resourcePath
     */
    public function setResourcePath($resourcePath)
    {
        $this->resourcePath = \strtolower($resourcePath);
    }
    /**
     * @return string
     * @throws ApiException
     */
    public function getResourcePath()
    {
        if (\strpos($this->resourcePath, "_") !== \false) {
            list($parentResource, $childResource) = \explode("_", $this->resourcePath, 2);
            if (empty($this->parentId)) {
                throw new \MolliePrefix\Mollie\Api\Exceptions\ApiException("Subresource '{$this->resourcePath}' used without parent '{$parentResource}' ID.");
            }
            return "{$parentResource}/{$this->parentId}/{$childResource}";
        }
        return $this->resourcePath;
    }
    /**
     * @param array $body
     * @return null|string
     * @throws ApiException
     */
    protected function parseRequestBody(array $body)
    {
        if (empty($body)) {
            return null;
        }
        try {
            $encoded = \MolliePrefix\GuzzleHttp\json_encode($body);
        } catch (\InvalidArgumentException $e) {
            throw new \MolliePrefix\Mollie\Api\Exceptions\ApiException("Error encoding parameters into JSON: '" . $e->getMessage() . "'.");
        }
        return $encoded;
    }
}
