<?php

declare(strict_types=1);

namespace Mollie\Subscription\DTO;

use JsonSerializable;
use Mollie\Subscription\Config\Config;
use Webmozart\Assert\Assert;

class CreateMandateData implements JsonSerializable
{
    /**
     * one of MandateMethod::class methods
     *
     * @var string
     */
    private $method;

    /** @var string */
    private $customerName;

    /** @var string */
    private $customerId;

    public function __construct(
        string $customerId,
        string $method,
        string $customerName
    ) {
        Assert::inArray($method, Config::getAvailableMandateMethods());
        $this->customerId = $customerId;
        $this->method = $method;
        $this->customerName = $customerName;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function jsonSerialize(): array
    {
        return [
            'method' => $this->method,
            'consumerName' => $this->customerName,
        ];
    }
}
