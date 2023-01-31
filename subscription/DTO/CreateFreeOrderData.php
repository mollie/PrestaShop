<?php

declare(strict_types=1);

namespace Mollie\Subscription\DTO;

use JsonSerializable;
use Mollie\Api\Types\SequenceType;

class CreateFreeOrderData implements JsonSerializable
{
    /** @var string */
    private $currencyIso;
    /** @var string */
    private $description;
    /** @var string */
    private $redirectUrl;
    /** @var string */
    private $method;
    /** @var string */
    private $customerId;
    /** @var string */
    private $webhookUrl;

    public function __construct(
        string $currencyIso,
        string $description,
        string $redirectUrl,
        string $webhookUrl,
        string $method,
        string $customerId
    ) {
        $this->currencyIso = $currencyIso;
        $this->description = $description;
        $this->redirectUrl = $redirectUrl;
        $this->method = $method;
        $this->customerId = $customerId;
        $this->webhookUrl = $webhookUrl;
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => [
                'currency' => $this->currencyIso,
                'value' => '0.00',
            ],
            'description' => $this->description,
            'redirectUrl' => $this->redirectUrl,
            'method' => $this->method,
            'sequenceType' => SequenceType::SEQUENCETYPE_FIRST,
            'customerId' => $this->customerId,
            'webhookUrl' => $this->webhookUrl,
        ];
    }
}
