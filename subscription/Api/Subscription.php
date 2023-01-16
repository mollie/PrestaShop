<?php

declare(strict_types=1);

namespace Mollie\Subscription\Api;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Subscription as MollieSubscription;
use Mollie\Subscription\DTO\CancelSubscriptionData;
use Mollie\Subscription\DTO\CreateSubscriptionData;
use Mollie\Subscription\DTO\GetSubscriptionData;
use Mollie\Subscription\Exception\SubscriptionApiException;
use Mollie\Subscription\Factory\MollieApi;

class Subscription
{
    /** @var MollieApiClient */
    private $apiClient;

    public function __construct(MollieApi $mollieApiFactory)
    {
        $this->apiClient = $mollieApiFactory->getMollieClient();
    }

    /**
     * @throws SubscriptionApiException
     */
    public function subscribeOrder(CreateSubscriptionData $subscriptionData): MollieSubscription
    {
        try {
            return $this->apiClient->subscriptions->createForId($subscriptionData->getCustomerId(), $subscriptionData->jsonSerialize());
        } catch (ApiException $e) {
            throw new SubscriptionApiException('Failed to create subscription', SubscriptionApiException::CREATION_FAILED, $e);
        }
    }

    /**
     * @throws SubscriptionApiException
     */
    public function cancelSubscription(CancelSubscriptionData $subscriptionData): MollieSubscription
    {
        try {
            return $this->apiClient->subscriptions->cancelForId($subscriptionData->getCustomerId(), $subscriptionData->getSubscriptionId());
        } catch (ApiException $e) {
            throw new SubscriptionApiException('Failed to cancel subscription', SubscriptionApiException::CANCELLATION_FAILED, $e);
        }
    }

    public function getSubscription(GetSubscriptionData $subscriptionData): MollieSubscription
    {
        try {
            return $this->apiClient->subscriptions->getForId($subscriptionData->getCustomerId(), $subscriptionData->getSubscriptionId());
        } catch (ApiException $e) {
            throw new SubscriptionApiException('Failed to cancel subscription', SubscriptionApiException::CANCELLATION_FAILED, $e);
        }
    }
}
