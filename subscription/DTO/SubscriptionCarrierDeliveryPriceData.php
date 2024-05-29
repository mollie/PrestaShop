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

class SubscriptionCarrierDeliveryPriceData
{
    /** @var int */
    private $deliveryAddressId;
    /** @var int */
    private $cartId;
    /** @var int */
    private $customerId;
    /** @var array */
    private $subscriptionProduct;
    /** @var int */
    private $subscriptionCarrierId;

    private function __construct(
        int $deliveryAddressId,
        int $cartId,
        int $customerId,
        array $subscriptionProduct,
        int $subscriptionCarrierId
    ) {
        $this->deliveryAddressId = $deliveryAddressId;
        $this->cartId = $cartId;
        $this->customerId = $customerId;
        $this->subscriptionProduct = $subscriptionProduct;
        $this->subscriptionCarrierId = $subscriptionCarrierId;
    }

    /**
     * @return int
     */
    public function getDeliveryAddressId(): int
    {
        return $this->deliveryAddressId;
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
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @return array
     */
    public function getSubscriptionProduct(): array
    {
        return $this->subscriptionProduct;
    }

    /**
     * @return int
     */
    public function getSubscriptionCarrierId(): int
    {
        return $this->subscriptionCarrierId;
    }

    public static function create(
        int $deliveryAddressId,
        int $cartId,
        int $customerId,
        array $subscriptionProduct,
        int $subscriptionCarrierId
    ): self {
        return new self(
            $deliveryAddressId,
            $cartId,
            $customerId,
            $subscriptionProduct,
            $subscriptionCarrierId
        );
    }
}
