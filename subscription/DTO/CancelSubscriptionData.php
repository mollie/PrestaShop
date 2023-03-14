<?php

declare(strict_types=1);

namespace Mollie\Subscription\DTO;

use JsonSerializable;

class CancelSubscriptionData implements JsonSerializable
{
    /** @var string */
    private $customerId;

    /** @var string */
    private $subscriptionId;

    /**
     * @param string $customerId
     * @param string $subscriptionId
     */
    public function __construct(string $customerId, string $subscriptionId)
    {
        $this->customerId = $customerId;
        $this->subscriptionId = $subscriptionId;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}
