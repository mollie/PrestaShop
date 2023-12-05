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

namespace Mollie\Subscription\DTO;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SubscriptionCarrierProviderData
{
    /** @var string */
    private $mollieCustomerId;
    /** @var string */
    private $mollieSubscriptionId;
    /** @var int */
    private $subscriptionCarrierId;
    /** @var int */
    private $orderId;

    private function __construct(
        string $mollieCustomerId,
        string $mollieSubscriptionId,
        int $subscriptionCarrierId,
        int $orderId
    ) {
        $this->mollieCustomerId = $mollieCustomerId;
        $this->mollieSubscriptionId = $mollieSubscriptionId;
        $this->subscriptionCarrierId = $subscriptionCarrierId;
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getMollieCustomerId(): string
    {
        return $this->mollieCustomerId;
    }

    /**
     * @return string
     */
    public function getMollieSubscriptionId(): string
    {
        return $this->mollieSubscriptionId;
    }

    /**
     * @return int
     */
    public function getSubscriptionCarrierId(): int
    {
        return $this->subscriptionCarrierId;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public static function create(
        string $mollieCustomerId,
        string $mollieSubscriptionId,
        int $subscriptionCarrierId,
        int $orderId
    ): self {
        return new self(
            $mollieCustomerId,
            $mollieSubscriptionId,
            $subscriptionCarrierId,
            $orderId
        );
    }
}
