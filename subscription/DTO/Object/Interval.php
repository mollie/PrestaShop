<?php

declare(strict_types=1);

namespace Mollie\Subscription\DTO\Object;

use JsonSerializable;
use Mollie\Subscription\Constants\IntervalConstant;
use Webmozart\Assert\Assert;

class Interval implements JsonSerializable
{
    /** @var int */
    private $amount;

    /**
     * @description use IntervalConstant::class const for values
     *
     * @var string
     */
    private $intervalValue;

    /**
     * @param int $amount
     * @param string $intervalValue
     */
    public function __construct(int $amount, string $intervalValue)
    {
        Assert::greaterThanEq($amount, 0, 'Interval amount cannot be negative');

        $this->amount = $amount;
        $this->intervalValue = $intervalValue;
    }

    public function jsonSerialize(): string
    {
        return "{$this->amount} {$this->intervalValue}";
    }
}
