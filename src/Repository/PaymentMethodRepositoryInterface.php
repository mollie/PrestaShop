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

namespace Mollie\Repository;

interface PaymentMethodRepositoryInterface extends ReadOnlyRepositoryInterface
{
    public function getPaymentMethodIssuersByPaymentMethodId($paymentMethodId);

    public function deletePaymentMethodIssuersByPaymentMethodId($paymentMethodId);

    public function deleteOldPaymentMethods(array $savedPaymentMethods, $environment, int $shopId);

    public function getPaymentMethodIdByMethodId($paymentMethodId, $environment, $shopId = null);

    public function getPaymentBy($column, $value);

    public function getMethodsForCheckout($environment, $shopId);

    public function updateTransactionId($oldTransactionId, $newTransactionId);

    public function savePaymentStatus($transactionId, $status, $orderId, $paymentMethod);

    public function addOpenStatusPayment($cartId, $orderPayment, $transactionId, $orderId, $orderReference);

    public function updatePaymentReason($transactionId, $reason);
}
