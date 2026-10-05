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

    public function __construct(
        string $id,
        string $clientAccessToken,
        string $status,
        string $amountValue,
        string $amountCurrency,
        string $createdAt
    ) {
        $this->id = $id;
        $this->clientAccessToken = $clientAccessToken;
        $this->status = $status;
        $this->amountValue = $amountValue;
        $this->amountCurrency = $amountCurrency;
        $this->createdAt = $createdAt;
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
            (string) ($session->createdAt ?? '')
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

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_CREATED], true);
    }

    public function isCompleted(): bool
    {
        return self::STATUS_COMPLETED === $this->status;
    }
}
