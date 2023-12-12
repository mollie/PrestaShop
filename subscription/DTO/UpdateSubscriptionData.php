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

use Mollie\Subscription\DTO\Object\Amount;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UpdateSubscriptionData
{
    /** @var string */
    private $mollieCustomerId;
    /** @var string */
    private $mollieSubscriptionId;
    /** @var Amount */
    private $orderAmount;
    /** @var int */
    private $customerId;
    /** @var int */
    private $cartId;
    /** @var int */
    private $subscriptionCarrierId;

    private function __construct(
        string $mollieCustomerId,
        string $mollieSubscriptionId,
        Amount $orderAmount,
        int $customerId,
        int $cartId,
        int $subscriptionCarrierId
    ) {
        $this->mollieCustomerId = $mollieCustomerId;
        $this->mollieSubscriptionId = $mollieSubscriptionId;
        $this->orderAmount = $orderAmount;
        $this->customerId = $customerId;
        $this->cartId = $cartId;
        $this->subscriptionCarrierId = $subscriptionCarrierId;
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
     * @return Amount
     */
    public function getOrderAmount(): Amount
    {
        return $this->orderAmount;
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return int
     */
    public function getCartId(): int
    {
        return $this->cartId;
    }

    /**
     * @return int
     */
    public function getSubscriptionCarrierId(): int
    {
        return $this->subscriptionCarrierId;
    }

    public static function create(
        string $mollieCustomerId,
        string $mollieSubscriptionId,
        Amount $orderAmount,
        int $customerId,
        int $cartId,
        int $subscriptionCarrierId
    ): self {
        return new self(
            $mollieCustomerId,
            $mollieSubscriptionId,
            $orderAmount,
            $customerId,
            $cartId,
            $subscriptionCarrierId
        );
    }
}
