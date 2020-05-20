<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Invoice;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\InvoiceCollection;
use Mollie\Api\Resources\BaseCollection;
use Mollie\Api\Resources\BaseResource;
use stdClass;

class InvoiceEndpoint extends CollectionEndpointAbstract
{
    protected $resourcePath = "invoices";
    /**
     * Get the object that is used by this API. Every API uses one type of object.
     *
     * @return BaseResource
     */
    protected function getResourceObject()
    {
        return new Invoice($this->client);
    }
    /**
     * Get the collection object that is used by this API. Every API uses one type of collection object.
     *
     * @param int $count
     * @param stdClass $_links
     *
     * @return BaseCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new InvoiceCollection($this->client, $count, $_links);
    }
    /**
     * Retrieve an Invoice from Mollie.
     *
     * Will throw a ApiException if the invoice id is invalid or the resource cannot be found.
     *
     * @param string $invoiceId
     * @param array $parameters
     *
     * @return Invoice
     * @throws ApiException
     */
    public function get($invoiceId, array $parameters = [])
    {
        return $this->rest_read($invoiceId, $parameters);
    }
    /**
     * Retrieves a collection of Invoices from Mollie.
     *
     * @param string $from The first invoice ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return InvoiceCollection
     * @throws ApiException
     */
    public function page($from = null, $limit = null, array $parameters = [])
    {
        return $this->rest_list($from, $limit, $parameters);
    }
    /**
     * This is a wrapper method for page
     *
     * @param array|null $parameters
     *
     * @return BaseCollection
     */
    public function all(array $parameters = [])
    {
        return $this->page(null, null, $parameters);
    }
}
