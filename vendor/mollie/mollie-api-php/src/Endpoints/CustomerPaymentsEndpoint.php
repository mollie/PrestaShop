<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Customer;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Payment;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\PaymentCollection;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\BaseResource;
use stdClass;

class CustomerPaymentsEndpoint extends CollectionEndpointAbstract
{
    protected $resourcePath = "customers_payments";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Payment
     */
    protected function getResourceObject()
    {
        return new Payment($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param stdClass $_links
     *
     * @return PaymentCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new PaymentCollection($this->client, $count, $_links);
    }
    /**
     * Create a subscription for a Customer
     *
     * @param Customer $customer
     * @param array $options
     * @param array $filters
     *
     * @return Payment
     * @throws ApiException
     */
    public function createFor(Customer $customer, array $options = [], array $filters = [])
    {
        return $this->createForId($customer->id, $options, $filters);
    }
    /**
     * Create a subscription for a Customer ID
     *
     * @param string $customerId
     * @param array $options
     * @param array $filters
     *
     * @return BaseResource|\Mollie\Api\Resources\Payment
     * @throws ApiException
     */
    public function createForId($customerId, array $options = [], array $filters = [])
    {
        $this->parentId = $customerId;
        return parent::rest_create($options, $filters);
    }
    /**
     * @param Customer $customer
     * @param string $from The first resource ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return PaymentCollection
     * @throws ApiException
     */
    public function listFor(Customer $customer, $from = null, $limit = null, array $parameters = [])
    {
        return $this->listForId($customer->id, $from, $limit, $parameters);
    }
    /**
     * @param string $customerId
     * @param string $from The first resource ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return BaseCollection|\Mollie\Api\Resources\PaymentCollection
     * @throws ApiException
     */
    public function listForId($customerId, $from = null, $limit = null, array $parameters = [])
    {
        $this->parentId = $customerId;
        return parent::rest_list($from, $limit, $parameters);
    }
}
