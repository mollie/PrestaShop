<?php

declare(strict_types=1);

namespace Mollie\Subscription\DTO\Object;

use JsonSerializable;
use Webmozart\Assert\Assert;

class Amount implements JsonSerializable
{
    /** @var float */
    private $value;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param float $value
     * @param string $currency
     */
    public function __construct(float $value, string $currency)
    {
        Assert::greaterThanEq($value, 0, 'Amount Value cannot be negative');

        $this->value = $value;
        $this->currency = $currency;
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => (string) $this->value,
            'currency' => $this->currency,
        ];
    }
}
