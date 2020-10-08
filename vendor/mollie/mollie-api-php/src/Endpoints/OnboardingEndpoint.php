<?php

namespace MolliePrefix\Mollie\Api\Endpoints;

use MolliePrefix\Mollie\Api\Exceptions\ApiException;
use MolliePrefix\Mollie\Api\Resources\BaseResource;
use MolliePrefix\Mollie\Api\Resources\Onboarding;
use MolliePrefix\Mollie\Api\Resources\ResourceFactory;
class OnboardingEndpoint extends \MolliePrefix\Mollie\Api\Endpoints\EndpointAbstract
{
    protected $resourcePath = "onboarding/me";
    protected function getResourceCollectionObject($count, $links)
    {
        throw new \BadMethodCallException('not implemented');
    }
    /**
     * Get the object that is used by this API endpoint. Every API endpoint uses one type of object.
     *
     * @return BaseResource
     */
    protected function getResourceObject()
    {
        return new \MolliePrefix\Mollie\Api\Resources\Onboarding($this->client);
    }
    /**
     * Retrieve the organization's onboarding status from Mollie.
     *
     * Will throw a ApiException if the resource cannot be found.
     *
     * @return Onboarding
     * @throws ApiException
     */
    public function get()
    {
        return $this->rest_read('', []);
    }
    /**
     * Submit data that will be prefilled in the merchant’s onboarding.
     * Please note that the data you submit will only be processed when the onboarding status is needs-data.
     *
     * Information that the merchant has entered in their dashboard will not be overwritten.
     *
     * Will throw a ApiException if the resource cannot be found.
     *
     * @return void
     * @throws ApiException
     */
    public function submit(array $parameters = [])
    {
        return $this->rest_create($parameters, []);
    }
    protected function rest_read($id, array $filters)
    {
        $result = $this->client->performHttpCall(self::REST_READ, $this->getResourcePath() . $this->buildQueryString($filters));
        return \MolliePrefix\Mollie\Api\Resources\ResourceFactory::createFromApiResult($result, $this->getResourceObject());
    }
    protected function rest_create(array $body, array $filters)
    {
        $this->client->performHttpCall(self::REST_CREATE, $this->getResourcePath() . $this->buildQueryString($filters), $this->parseRequestBody($body));
    }
}
