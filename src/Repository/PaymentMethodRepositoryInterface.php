<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Repository;

interface PaymentMethodRepositoryInterface extends ReadOnlyRepositoryInterface
{
	public function getPaymentMethodIssuersByPaymentMethodId($paymentMethodId);

	public function deletePaymentMethodIssuersByPaymentMethodId($paymentMethodId);

	public function deleteOldPaymentMethods(array $savedPaymentMethods, $environment);

	public function getPaymentMethodIdByMethodId($paymentMethodId, $environment);

	public function getPaymentBy($column, $id);

	public function tryAddOrderReferenceColumn();

	public function getMethodsForCheckout($environment);

	public function updateTransactionId($oldTransactionId, $newTransactionId);

	public function savePaymentStatus($transactionId, $status, $orderId, $paymentMethod);

	public function addOpenStatusPayment($cartId, $orderPayment, $transactionId, $orderId, $orderReference);
}
