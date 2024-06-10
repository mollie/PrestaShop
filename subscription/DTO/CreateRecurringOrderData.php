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

class CreateRecurringOrderData
{
    /** @var int */
    private $recurringOrdersProductId;
    /** @var int */
    private $orderId;
    /** @var int */
    private $cartId;
    /** @var int */
    private $currencyId;
    /** @var int */
    private $customerId;
    /** @var int */
    private $deliveryAddressId;
    /** @var int */
    private $invoiceAddressId;
    /** @var string */
    private $description;
    /** @var string */
    private $status;
    /** @var float */
    private $subscriptionTotalAmount;
    /** @var string */
    private $method;
    /** @var string */
    private $nextPayment;
    /** @var string */
    private $reminderAt;
    /** @var string */
    private $cancelledAt;
    /** @var string */
    private $mollieSubscriptionId;
    /** @var string */
    private $mollieCustomerId;

    private function __construct(
        int $recurringOrdersProductId,
        int $orderId,
        int $cartId,
        int $currencyId,
        int $customerId,
        int $deliveryAddressId,
        int $invoiceAddressId,
        string $description,
        string $status,
        float $subscriptionTotalAmount,
        string $method,
        string $nextPayment,
        string $reminderAt,
        string $cancelledAt,
        string $mollieSubscriptionId,
        string $mollieCustomerId
    ) {
        $this->recurringOrdersProductId = $recurringOrdersProductId;
        $this->orderId = $orderId;
        $this->cartId = $cartId;
        $this->currencyId = $currencyId;
        $this->customerId = $customerId;
        $this->deliveryAddressId = $deliveryAddressId;
        $this->invoiceAddressId = $invoiceAddressId;
        $this->description = $description;
        $this->status = $status;
        $this->subscriptionTotalAmount = $subscriptionTotalAmount;
        $this->method = $method;
        $this->nextPayment = $nextPayment;
        $this->reminderAt = $reminderAt;
        $this->cancelledAt = $cancelledAt;
        $this->mollieSubscriptionId = $mollieSubscriptionId;
        $this->mollieCustomerId = $mollieCustomerId;
    }

    /**
     * @return int
     */
    public function getRecurringOrdersProductId(): int
    {
        return $this->recurringOrdersProductId;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
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
    public function getCurrencyId(): int
    {
        return $this->currencyId;
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
    public function getDeliveryAddressId(): int
    {
        return $this->deliveryAddressId;
    }

    /**
     * @return int
     */
    public function getInvoiceAddressId(): int
    {
        return $this->invoiceAddressId;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return float
     */
    public function getSubscriptionTotalAmount(): float
    {
        return $this->subscriptionTotalAmount;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getNextPayment(): string
    {
        return $this->nextPayment;
    }

    /**
     * @return string
     */
    public function getReminderAt(): string
    {
        return $this->reminderAt;
    }

    /**
     * @return string
     */
    public function getCancelledAt(): string
    {
        return $this->cancelledAt;
    }

    /**
     * @return string
     */
    public function getMollieSubscriptionId(): string
    {
        return $this->mollieSubscriptionId;
    }

    /**
     * @return string
     */
    public function getMollieCustomerId(): string
    {
        return $this->mollieCustomerId;
    }

    public static function create(
        int $recurringOrdersProductId,
        int $orderId,
        int $cartId,
        int $currencyId,
        int $customerId,
        int $deliveryAddressId,
        int $invoiceAddressId,
        string $description,
        string $status,
        float $subscriptionTotalAmount,
        string $method,
        string $nextPayment,
        string $reminderAt,
        string $cancelledAt,
        string $mollieSubscriptionId,
        string $mollieCustomerId
    ): self {
        return new self(
            $recurringOrdersProductId,
            $orderId,
            $cartId,
            $currencyId,
            $customerId,
            $deliveryAddressId,
            $invoiceAddressId,
            $description,
            $status,
            $subscriptionTotalAmount,
            $method,
            $nextPayment,
            $reminderAt,
            $cancelledAt,
            $mollieSubscriptionId,
            $mollieCustomerId
        );
    }
}
