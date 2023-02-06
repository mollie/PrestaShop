<?php

declare(strict_types=1);

namespace Mollie\Subscription\DTO;

use JsonSerializable;

class UpdateSubscriptionData implements JsonSerializable
{
    /** @var string */
    private $customerId;

    /** @var string */
    private $subscriptionId;

    /** @var string */
    private $mandateId;

    /**
     * @param string $customerId
     * @param string $subscriptionId
     */
    public function __construct(string $customerId, string $subscriptionId, string $mandateId)
    {
        $this->customerId = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->mandateId = $mandateId;
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
        return [
            'mandateId' => $this->mandateId,
        ];
    }
}
