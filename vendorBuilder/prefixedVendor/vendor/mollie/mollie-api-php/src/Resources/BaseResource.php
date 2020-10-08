<?php

namespace MolliePrefix\Mollie\Api\Resources;

use MolliePrefix\Mollie\Api\MollieApiClient;
abstract class BaseResource
{
    /**
     * @var MollieApiClient
     */
    protected $client;
    /**
     * @param $client
     */
    public function __construct(\MolliePrefix\Mollie\Api\MollieApiClient $client)
    {
        $this->client = $client;
    }
}
