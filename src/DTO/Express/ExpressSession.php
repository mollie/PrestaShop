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

namespace Mollie\DTO\Express;

use Mollie\Api\Resources\Session;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The part of a Checkout Session the module reads. The only class that knows Mollie's response
 * field names. The response carries no payment id: that arrives with the payment webhook.
 */
class ExpressSession
{
    const STATUS_OPEN = 'open';
    const STATUS_CREATED = 'created';
    const STATUS_READY_FOR_PROCESSING = 'ready_for_processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FAILED = 'failed';

    /** @var string */
    private $id;
    /** @var string */
    private $clientAccessToken;
    /** @var string */
    private $status;
    /** @var string */
    private $amountValue;
    /** @var string */
    private $amountCurrency;
    /** @var string */
    private $createdAt;
    /** @var string */
    private $description;

    public function __construct(
        string $id,
        string $clientAccessToken,
        string $status,
        string $amountValue,
        string $amountCurrency,
        string $createdAt,
        string $description = ''
    ) {
        $this->id = $id;
        $this->clientAccessToken = $clientAccessToken;
        $this->status = $status;
        $this->amountValue = $amountValue;
        $this->amountCurrency = $amountCurrency;
        $this->createdAt = $createdAt;
        $this->description = $description;
    }

    public static function fromResource(Session $session): self
    {
        $amount = $session->amount ?? null;

        return new self(
            (string) ($session->id ?? ''),
            (string) ($session->clientAccessToken ?? ''),
            (string) ($session->status ?? ''),
            (string) ($amount->value ?? ''),
            (string) ($amount->currency ?? ''),
            (string) ($session->createdAt ?? ''),
            (string) ($session->description ?? '')
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClientAccessToken(): string
    {
        return $this->clientAccessToken;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAmountValue(): string
    {
        return $this->amountValue;
    }

    public function getAmountCurrency(): string
    {
        return $this->amountCurrency;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * The payment created through this session carries it as its description, and so does its
     * mollie_payments row (order_reference) until an order exists.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * A session the shopper can still pay through, including one Mollie is processing right now:
     * replacing that one would drop the row of the session actually being paid.
     */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_CREATED, self::STATUS_READY_FOR_PROCESSING], true);
    }

    public function isCompleted(): bool
    {
        return self::STATUS_COMPLETED === $this->status;
    }
}
