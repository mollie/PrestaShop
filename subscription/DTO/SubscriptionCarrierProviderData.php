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
    /** @var int */
    private $mollieCustomerId;
    /** @var int */
    private $mollieSubscriptionId;
    /** @var int */
    private $subscriptionCarrierId;
    /** @var int */
    private $orderId;

    private function __construct(
        int $mollieCustomerId,
        int $mollieSubscriptionId,
        int $subscriptionCarrierId,
        int $orderId
    ) {
        $this->mollieCustomerId = $mollieCustomerId;
        $this->mollieSubscriptionId = $mollieSubscriptionId;
        $this->subscriptionCarrierId = $subscriptionCarrierId;
        $this->orderId = $orderId;
    }

    /**
     * @return int
     */
    public function getMollieCustomerId(): int
    {
        return $this->mollieCustomerId;
    }

    /**
     * @return int
     */
    public function getMollieSubscriptionId(): int
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
        int $mollieCustomerId,
        int $mollieSubscriptionId,
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
