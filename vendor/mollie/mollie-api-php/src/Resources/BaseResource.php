<?php

namespace _PhpScoper5ece82d7231e4\Mollie\Api\Resources;

use _PhpScoper5ece82d7231e4\Mollie\Api\MollieApiClient;
abstract class BaseResource
{
    /**
     * @var MollieApiClient
     */
    protected $client;
    /**
     * @param $client
     */
    public function __construct(\_PhpScoper5ece82d7231e4\Mollie\Api\MollieApiClient $client)
    {
        $this->client = $client;
    }
}
