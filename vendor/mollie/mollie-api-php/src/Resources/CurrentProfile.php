<?php

namespace MolliePrefix\Mollie\Api\Resources;

use MolliePrefix\Mollie\Api\Exceptions\ApiException;
class CurrentProfile extends \MolliePrefix\Mollie\Api\Resources\Profile
{
    /**
     * Enable a payment method for this profile.
     *
     * @param string $methodId
     * @param array $data
     * @return Method
     * @throws ApiException
     */
    public function enableMethod($methodId, array $data = [])
    {
        return $this->client->profileMethods->createForCurrentProfile($methodId, $data);
    }
    /**
     * Disable a payment method for this profile.
     *
     * @param string $methodId
     * @param array $data
     * @return Method
     * @throws ApiException
     */
    public function disableMethod($methodId, array $data = [])
    {
        return $this->client->profileMethods->deleteForCurrentProfile($methodId, $data);
    }
}
