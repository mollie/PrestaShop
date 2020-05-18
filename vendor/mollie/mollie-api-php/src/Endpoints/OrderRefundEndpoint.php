<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Order;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\Refund;
use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\RefundCollection;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\BaseResource;
use stdClass;

class OrderRefundEndpoint extends CollectionEndpointAbstract
{
    protected $resourcePath = "orders_refunds";
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return Refund
     */
    protected function getResourceObject()
    {
        return new Refund($this->client);
    }
    /**
     * Get the collection object that is used by this API endpoint. Every API endpoint uses one type of collection object.
     *
     * @param int $count
     * @param stdClass $_links
     *
     * @return RefundCollection
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new RefundCollection($this->client, $count, $_links);
    }
    /**
     * Refund some order lines. You can provide an empty array for the
     * "lines" data to refund all eligible lines for this order.
     *
     * @param Order $order
     * @param array $data
     * @param array $filters
     *
     * @return Refund
     * @throws ApiException
     */
    public function createFor(Order $order, array $data, array $filters = [])
    {
        return $this->createForId($order->id, $data, $filters);
    }
    /**
     * Refund some order lines. You can provide an empty array for the
     * "lines" data to refund all eligible lines for this order.
     *
     * @param string $orderId
     * @param array $data
     * @param array $filters
     *
     * @return BaseResource|\Mollie\Api\Resources\Refund
     * @throws ApiException
     */
    public function createForId($orderId, array $data, array $filters = [])
    {
        $this->parentId = $orderId;
        return parent::rest_create($data, $filters);
    }
}
