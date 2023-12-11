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

use Mollie\Subscription\DTO\Object\Amount;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateSubscriptionData
{
    /** @var string */
    private $customerId;
    /** @var string */
    private $subscriptionId;
    /** @var ?string */
    private $mandateId;
    /** @var ?array */
    private $metadata;
    /** @var ?Amount */
    private $amount;

    public function __construct(
        string $customerId,
        string $subscriptionId,
        string $mandateId = null,
        array $metadata = null,
        Amount $amount = null
    ) {
        $this->customerId = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->mandateId = $mandateId;
        $this->metadata = $metadata;
        $this->amount = $amount;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    public function toArray(): array
    {
        $data = [
            'mandateId' => $this->mandateId,
            'metadata' => $this->metadata,
            'amount' => $this->amount ? $this->amount->toArray() : null,
        ];

        return array_filter($data, static function ($val) {
            return !empty($val);
        });
    }
}
