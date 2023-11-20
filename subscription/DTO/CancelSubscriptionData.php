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

if (!defined('_PS_VERSION_')) {
    exit;
}

class CancelSubscriptionData implements JsonSerializable
{
    /** @var string */
    private $customerId;

    /** @var string */
    private $subscriptionId;

    /**
     * @param string $customerId
     * @param string $subscriptionId
     */
    public function __construct(string $customerId, string $subscriptionId)
    {
        $this->customerId = $customerId;
        $this->subscriptionId = $subscriptionId;
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
        return [];
    }
}
