<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

declare(strict_types=1);

namespace Mollie\Subscription\DTO;

use JsonSerializable;
use Mollie\Subscription\DTO\Object\Amount;
use Mollie\Subscription\DTO\Object\Interval;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateSubscriptionData implements JsonSerializable
{
    /** @var string */
    private $customerId;

    /** @var Amount */
    private $amount;

    /** @var int */
    private $times;

    /** @var Interval */
    private $interval;

    /**
     * format: YYYY-MM-DD
     *
     * @var string
     */
    private $startDate;

    /** @var string */
    private $description;

    /**
     * @description use SubscriptionAvailableMethods::class methods
     *
     * @var string
     */
    private $method;

    /** @var string */
    private $webhookUrl;

    /** @var array */
    private $metaData;

    /** @var string */
    private $mandateId;

    public function __construct(string $customerId, Amount $amount, Interval $interval, string $description)
    {
        $this->customerId = $customerId;
        $this->amount = $amount;
        $this->interval = $interval;
        $this->description = $description;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function setMandateId(string $mandateId): void
    {
        $this->mandateId = $mandateId;
    }

    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): void
    {
        $this->metaData = $metaData;
    }

    public function jsonSerialize(): array
    {
        $json = [];
        $json['amount'] = $this->amount->jsonSerialize();
        $json['interval'] = $this->interval->jsonSerialize();
        $json['description'] = $this->description;

        $json['times'] = $this->times;
        $json['startDate'] = $this->startDate;
        $json['method'] = $this->method;
        $json['webhookUrl'] = $this->webhookUrl;
        $json['metadata'] = $this->metaData;
        $json['mandateId'] = $this->mandateId;

        return array_filter($json, function ($val) {
            return $val !== null;
        });
    }
}
