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

class SubscriptionOrderAmountProviderData
{
    /** @var int */
    private $addressDeliveryId;
    /** @var int */
    private $cartId;
    /** @var int */
    private $customerId;
    /** @var array */
    private $subscriptionProduct;
    /** @var int */
    private $subscriptionCarrierId;
    /** @var int */
    private $currencyId;

    private function __construct(
        int $addressDeliveryId,
        int $cartId,
        int $customerId,
        array $subscriptionProduct,
        int $subscriptionCarrierId,
        int $currencyId
    ) {
        $this->addressDeliveryId = $addressDeliveryId;
        $this->cartId = $cartId;
        $this->customerId = $customerId;
        $this->subscriptionProduct = $subscriptionProduct;
        $this->subscriptionCarrierId = $subscriptionCarrierId;
        $this->currencyId = $currencyId;
    }

    /**
     * @return int
     */
    public function getAddressDeliveryId(): int
    {
        return $this->addressDeliveryId;
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

    /**
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    public static function create(
        int $addressDeliveryId,
        int $cartId,
        int $customerId,
        array $subscriptionProduct,
        int $subscriptionCarrierId,
        int $currencyId
    ): self {
        return new self(
            $addressDeliveryId,
            $cartId,
            $customerId,
            $subscriptionProduct,
            $subscriptionCarrierId,
            $currencyId
        );
    }
}
