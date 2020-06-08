<?php

namespace _PhpScoper5eddef0da618a\Mollie\Api\Resources;

use _PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException;
class CurrentProfile extends \_PhpScoper5eddef0da618a\Mollie\Api\Resources\Profile
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
