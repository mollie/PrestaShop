<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

use _PhpScoper5ea00cc67502b\Mollie\Api\MollieApiClient;
use Mollie\Api\Exceptions\ApiException;
use stdClass;

abstract class CursorCollection extends BaseCollection
{
    /**
     * @var MollieApiClient
     */
    protected $client;
    /**
     * @param MollieApiClient $client
     * @param int $count
     * @param stdClass $_links
     */
    public final function __construct(MollieApiClient $client, $count, $_links)
    {
        parent::__construct($count, $_links);
        $this->client = $client;
    }
    /**
     * @return BaseResource
     */
    protected abstract function createResourceObject();
    /**
     * Return the next set of resources when available
     *
     * @return CursorCollection|null
     * @throws ApiException
     */
    public final function next()
    {
        if (!$this->hasNext()) {
            return null;
        }
        $result = $this->client->performHttpCallToFullUrl(MollieApiClient::HTTP_GET, $this->_links->next->href);
        $collection = new static($this->client, $result->count, $result->_links);
        foreach ($result->_embedded->{$collection->getCollectionResourceName()} as $dataResult) {
            $collection[] = ResourceFactory::createFromApiResult($dataResult, $this->createResourceObject());
        }
        return $collection;
    }
    /**
     * Return the previous set of resources when available
     *
     * @return CursorCollection|null
     * @throws ApiException
     */
    public final function previous()
    {
        if (!$this->hasPrevious()) {
            return null;
        }
        $result = $this->client->performHttpCallToFullUrl(MollieApiClient::HTTP_GET, $this->_links->previous->href);
        $collection = new static($this->client, $result->count, $result->_links);
        foreach ($result->_embedded->{$collection->getCollectionResourceName()} as $dataResult) {
            $collection[] = ResourceFactory::createFromApiResult($dataResult, $this->createResourceObject());
        }
        return $collection;
    }
    /**
     * Determine whether the collection has a next page available.
     *
     * @return bool
     */
    public function hasNext()
    {
        return isset($this->_links->next->href);
    }
    /**
     * Determine whether the collection has a previous page available.
     *
     * @return bool
     */
    public function hasPrevious()
    {
        return isset($this->_links->previous->href);
    }
}
