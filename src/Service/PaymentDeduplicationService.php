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

namespace Mollie\Service;

use Cart;
use Db;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Service to prevent duplicate payment creation for the same cart.
 * Addresses race condition where multiple payment attempts can be made
 * for the same cart ID through multiple browser tabs or rapid requests.
 */
class PaymentDeduplicationService
{
    private const PENDING_PAYMENT_TIMEOUT_MINUTES = 30;

    /**
     * Check if cart already has a pending or completed payment.
     * This prevents users from creating multiple payments for the same cart.
     *
     * @param int $cartId
     *
     * @return array|false Returns pending payment data if exists, false otherwise
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getPendingPaymentForCart(int $cartId)
    {
        // First check if cart already has an order (most definitive check)
        $orderId = Order::getIdByCartId($cartId);
        if ($orderId) {
            $order = new Order($orderId);

            // Get the associated Mollie payment
            $sql = new \DbQuery();
            $sql->select('*');
            $sql->from('mollie_payments');
            $sql->where('cart_id = ' . (int) $cartId);
            $sql->orderBy('created_at DESC');

            $payment = Db::getInstance()->getRow($sql);

            if ($payment) {
                $payment['has_order'] = true;
                $payment['order_id'] = $orderId;

                return $payment;
            }
        }

        // Check for pending payments that don't have orders yet
        $timeoutTimestamp = date('Y-m-d H:i:s', strtotime('-' . self::PENDING_PAYMENT_TIMEOUT_MINUTES . ' minutes'));

        $sql = new \DbQuery();
        $sql->select('*');
        $sql->from('mollie_payments');
        $sql->where('cart_id = ' . (int) $cartId);
        $sql->where('bank_status IN ("' . pSQL(PaymentStatus::STATUS_OPEN) . '", "' . pSQL(PaymentStatus::STATUS_PENDING) . '")');
        $sql->where('created_at > "' . pSQL($timeoutTimestamp) . '"');
        $sql->orderBy('created_at DESC');

        $pendingPayment = Db::getInstance()->getRow($sql);

        if ($pendingPayment) {
            $pendingPayment['has_order'] = false;

            return $pendingPayment;
        }

        return false;
    }

    /**
     * Check if cart can have a new payment created.
     * Returns true if payment creation is allowed, false otherwise.
     *
     * @param int $cartId
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function canCreatePayment(int $cartId): bool
    {
        $existingPayment = $this->getPendingPaymentForCart($cartId);

        return $existingPayment === false;
    }

    /**
     * Get the checkout URL for an existing pending payment.
     * Allows redirecting users to complete an already-initiated payment
     * instead of creating a duplicate.
     *
     * @param array $paymentData
     *
     * @return string|null
     */
    public function getExistingPaymentCheckoutUrl(array $paymentData): ?string
    {
        if (empty($paymentData['transaction_id'])) {
            return null;
        }

        // In a real implementation, you would fetch the checkout URL from Mollie API
        // For now, return null to indicate the payment exists but URL needs to be fetched
        return null;
    }

    /**
     * Cancel expired pending payments for a cart.
     * This allows users to retry payment after timeout period.
     *
     * @param int $cartId
     *
     * @return bool
     */
    public function cancelExpiredPayments(int $cartId): bool
    {
        $timeoutTimestamp = date('Y-m-d H:i:s', strtotime('-' . self::PENDING_PAYMENT_TIMEOUT_MINUTES . ' minutes'));

        return Db::getInstance()->update(
            'mollie_payments',
            [
                'bank_status' => pSQL(PaymentStatus::STATUS_EXPIRED),
                'updated_at' => ['type' => 'sql', 'value' => 'NOW()'],
            ],
            'cart_id = ' . (int) $cartId . '
            AND bank_status IN ("' . pSQL(PaymentStatus::STATUS_OPEN) . '", "' . pSQL(PaymentStatus::STATUS_PENDING) . '")
            AND created_at < "' . pSQL($timeoutTimestamp) . '"'
        );
    }
}
