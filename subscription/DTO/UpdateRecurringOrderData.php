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

class UpdateRecurringOrderData
{
    /** @var int */
    private $mollieRecurringOrderId;
    /** @var float */
    private $subscriptionTotalAmount;

    private function __construct(
        int $mollieRecurringOrderId,
        float $subscriptionTotalAmount
    ) {
        $this->mollieRecurringOrderId = $mollieRecurringOrderId;
        $this->subscriptionTotalAmount = $subscriptionTotalAmount;
    }

    /**
     * @return int
     */
    public function getMollieRecurringOrderId(): int
    {
        return $this->mollieRecurringOrderId;
    }

    /**
     * @return float
     */
    public function getSubscriptionTotalAmount(): float
    {
        return $this->subscriptionTotalAmount;
    }

    public static function create(
        int $mollieRecurringOrderId,
        float $subscriptionTotalAmount
    ): self {
        return new self(
            $mollieRecurringOrderId,
            $subscriptionTotalAmount
        );
    }
}
