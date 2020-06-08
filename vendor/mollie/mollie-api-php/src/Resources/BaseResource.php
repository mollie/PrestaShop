<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Resources;

use _PhpScoper5eddef0da618a\Mollie\Api\MollieApiClient;
abstract class BaseResource
{
    /**
     * @var MollieApiClient
     */
    protected $client;
    /**
     * @param $client
     */
    public function __construct(\_PhpScoper5eddef0da618a\Mollie\Api\MollieApiClient $client)
    {
        $this->client = $client;
    }
}
