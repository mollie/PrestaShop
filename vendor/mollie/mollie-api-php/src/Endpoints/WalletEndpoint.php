<?php

namespace _PhpScoper5ece82d7231e4\Mollie\Api\Endpoints;

use _PhpScoper5ece82d7231e4\Mollie\Api\Resources\BaseResource;
class WalletEndpoint extends \_PhpScoper5ece82d7231e4\Mollie\Api\Endpoints\EndpointAbstract
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
        return \json_encode($response);
    }
}
