<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Permission;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\PermissionCollection;
class PermissionEndpoint extends \_PhpScoper5ea00cc67502b\Mollie\Api\Endpoints\CollectionEndpointAbstract
{
    protected $resourcePath = "permissions";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one
     * type of object.
     *
     * @return Permission
     */
    protected function getResourceObject()
    {
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\Permission($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API
     * endpoint uses one type of collection object.
     *
     * @param int $count
     * @param \stdClass $_links
     *
     * @return PermissionCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\PermissionCollection($count, $_links);
    }
    /**
     * Retrieve a single Permission from Mollie.
     *
     * Will throw an ApiException if the permission id is invalid.
     *
     * @param string $permissionId
     * @param array $parameters
     * @return Permission
     * @throws ApiException
     */
    public function get($permissionId, array $parameters = [])
    {
        return $this->rest_read($permissionId, $parameters);
    }
    /**
     * Retrieve all permissions.
     *
     * @param array $parameters
     *
     * @return PermissionCollection
     * @throws ApiException
     */
    public function all(array $parameters = [])
    {
        return parent::rest_list(null, null, $parameters);
    }
}
