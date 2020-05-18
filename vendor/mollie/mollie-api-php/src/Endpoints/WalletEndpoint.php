<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Endpoints;

use _PhpScoper5ea00cc67502b\Mollie\Api\Resources\BaseResource;
use function json_encode;

class WalletEndpoint extends EndpointAbstract
{
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return BaseResource
     */
    protected function getResourceObject()
    {
        // Not used
    }
    public function requestApplePayPaymentSession($domain, $validationUrl)
    {
        $body = $this->parseRequestBody(['domain' => $domain, 'validationUrl' => $validationUrl]);
        $response = $this->client->performHttpCall(self::REST_CREATE, 'wallets/applepay/sessions', $body);
        return json_encode($response);
    }
}
