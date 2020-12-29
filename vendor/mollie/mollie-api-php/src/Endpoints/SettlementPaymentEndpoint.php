<?php

namespace MolliePrefix\Mollie\Api\Endpoints;

use MolliePrefix\Mollie\Api\Resources\BaseCollection;
use MolliePrefix\Mollie\Api\Resources\Payment;
use MolliePrefix\Mollie\Api\Resources\PaymentCollection;
class SettlementPaymentEndpoint extends \MolliePrefix\Mollie\Api\Endpoints\CollectionEndpointAbstract
{
    protected $resourcePath = "settlements_payments";
    /**
     * @inheritDoc
     */
    protected function getResourceObject()
    {
        return new \MolliePrefix\Mollie\Api\Resources\Payment($this->client);
    }
    /**
     * @inheritDoc
     */
    protected function getResourceCollectionObject($count, $_links)
    {
        return new \MolliePrefix\Mollie\Api\Resources\PaymentCollection($this->client, $count, $_links);
    }
    /**
     * Retrieves a collection of Payments from Mollie.
     *
     * @param $settlementId
     * @param string $from The first payment ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return BaseCollection|PaymentCollection
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function pageForId($settlementId, $from = null, $limit = null, array $parameters = [])
    {
        $this->parentId = $settlementId;
        return $this->rest_list($from, $limit, $parameters);
    }
}
