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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Answer to the component's `submit` event: may Mollie create the payment for the mounted session.
 */
class ExpressSubmitCheck implements \JsonSerializable
{
    const REASON_ORDER_EXISTS = 'order_exists';
    const REASON_CART_NOT_READY = 'cart_not_ready';
    const REASON_AMOUNT_CHANGED = 'amount_changed';
    const REASON_CART_OWNER_CHANGED = 'cart_owner_changed';
    const REASON_PAYMENT_STARTED = 'payment_started';
    const REASON_SESSION_CHANGED = 'session_changed';

    /** @var bool */
    private $ok;
    /** @var string */
    private $reason;
    /** @var string */
    private $clientAccessToken;

    public function __construct(bool $ok, string $reason = '', string $clientAccessToken = '')
    {
        $this->ok = $ok;
        $this->reason = $reason;
        $this->clientAccessToken = $clientAccessToken;
    }

    public static function ok(): self
    {
        return new self(true);
    }

    public static function rejected(string $reason, string $clientAccessToken = ''): self
    {
        return new self(false, $reason, $clientAccessToken);
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getClientAccessToken(): string
    {
        return $this->clientAccessToken;
    }

    public function jsonSerialize(): array
    {
        $data = ['ok' => $this->ok];

        if ('' !== $this->reason) {
            $data['reason'] = $this->reason;
        }

        if ('' !== $this->clientAccessToken) {
            $data['clientAccessToken'] = $this->clientAccessToken;
        }

        return $data;
    }
}
