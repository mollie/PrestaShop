<?php

declare(strict_types=1);

namespace Mollie\Subscription\Api;

use Mollie\Api\MollieApiClient;
use Mollie\Subscription\DTO\CreateFreeOrderData;
use Mollie\Subscription\Factory\MollieApiFactory;

class PaymentApi
{
    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApiFactory $mollieApiFactory)
    {
        $this->apiClient = $mollieApiFactory->getMollieClient();
    }

    public function createFreePayment(CreateFreeOrderData $createFreeOrderData)
    {
        return $this->apiClient->payments->create($createFreeOrderData->jsonSerialize());
    }

    public function getPayment(string $transactionId)
    {
        return $this->apiClient->payments->get($transactionId);
    }
}
