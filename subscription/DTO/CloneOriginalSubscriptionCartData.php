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

class CloneOriginalSubscriptionCartData
{
    /** @var int */
    private $cartId;
    /** @var int */
    private $recurringOrderProductId;
    /** @var int */
    private $invoiceAddressId;
    /** @var int */
    private $deliveryAddressId;

    private function __construct(
        int $cartId,
        int $recurringOrderProductId,
        int $invoiceAddressId,
        int $deliveryAddressId
    ) {
        $this->cartId = $cartId;
        $this->recurringOrderProductId = $recurringOrderProductId;
        $this->invoiceAddressId = $invoiceAddressId;
        $this->deliveryAddressId = $deliveryAddressId;
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
    public function getRecurringOrderProductId(): int
    {
        return $this->recurringOrderProductId;
    }

    /**
     * @return int
     */
    public function getInvoiceAddressId(): int
    {
        return $this->invoiceAddressId;
    }

    /**
     * @return int
     */
    public function getDeliveryAddressId(): int
    {
        return $this->deliveryAddressId;
    }

    public static function create(
        int $cartId,
        int $recurringOrderProductId,
        int $invoiceAddressId,
        int $deliveryAddressId
    ): self {
        return new self(
            $cartId,
            $recurringOrderProductId,
            $invoiceAddressId,
            $deliveryAddressId
        );
    }
}
