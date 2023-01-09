<?php

declare(strict_types=1);

namespace Mollie\Subscription\Api;

use Mollie\Api\MollieApiClient;
use Mollie\Subscription\DTO\CreateMandateData;
use Mollie\Subscription\Factory\MollieApi;

class Mandate
{
    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApi $mollieApiFactory)
    {
        $this->apiClient = $mollieApiFactory->getMollieClient();
    }

    public function createMandate(CreateMandateData $mandateData): \Mollie\Api\Resources\Mandate
    {
        return $this->apiClient->mandates->createForId($mandateData->getCustomerId(), $mandateData->jsonSerialize());
    }
}
