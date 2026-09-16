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

namespace Mollie\Utility;

use Mollie\Config\Config;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Resolves the terminal status and failure reason of a payment attempt that never
 * produced a PrestaShop order.
 *
 * Pure by design: it reads an already hydrated API resource and makes no API call,
 * no database query and no container lookup. The webhook controller fetches orders
 * with ['embed' => 'payments'], so the embedded payments are already in memory.
 */
class PaymentAttemptUtility
{
    const REASON_MAX_LENGTH = 64;

    /**
     * Returns the terminal failure status to persist, or null when the attempt is
     * not in a terminal failure state.
     *
     * @param \Mollie\Api\Resources\Payment|\Mollie\Api\Resources\Order|object $apiResource
     *
     * @return string|null
     */
    public static function resolveFinalStatus($apiResource)
    {
        if (!is_object($apiResource) || !isset($apiResource->resource)) {
            return null;
        }

        if (Config::MOLLIE_API_STATUS_ORDER === $apiResource->resource) {
            return self::resolveOrderStatus($apiResource);
        }

        return self::terminalStatusOrNull(self::readProperty($apiResource, 'status'));
    }

    /**
     * Returns the machine readable failure code, or an empty string when the API
     * did not provide one. Machine codes are stored rather than the human messages
     * because those are localised to the payment locale.
     *
     * @param \Mollie\Api\Resources\Payment|\Mollie\Api\Resources\Order|object $apiResource
     *
     * @return string
     */
    public static function resolveFailureReason($apiResource)
    {
        $payment = self::resolvePaymentSource($apiResource);

        if (!is_object($payment)) {
            return '';
        }

        $code = self::extractReasonCode($payment);

        if ('' === $code) {
            return '';
        }

        return (string) Tools::substr($code, 0, self::REASON_MAX_LENGTH);
    }

    /**
     * The Orders API has no failed status, so a declined card leaves the order at
     * "created". Only canceled and expired are terminal at order level; every other
     * failure has to be read from the last payment attempt.
     *
     * @param object $apiOrder
     *
     * @return string|null
     */
    private static function resolveOrderStatus($apiOrder)
    {
        $orderStatus = self::readProperty($apiOrder, 'status');

        if (MollieStatusUtility::isPaymentFailed($orderStatus)) {
            return $orderStatus;
        }

        $lastPayment = self::getLastEmbeddedPayment($apiOrder);

        if (!is_object($lastPayment)) {
            return null;
        }

        return self::terminalStatusOrNull(self::readProperty($lastPayment, 'status'));
    }

    /**
     * @param object $apiResource
     *
     * @return object|null
     */
    private static function resolvePaymentSource($apiResource)
    {
        if (!is_object($apiResource)) {
            return null;
        }

        if (isset($apiResource->resource) && Config::MOLLIE_API_STATUS_ORDER === $apiResource->resource) {
            return self::getLastEmbeddedPayment($apiResource);
        }

        return $apiResource;
    }

    /**
     * Reads the embedded payments straight off the resource instead of calling
     * Order::payments(), which needs an API client and hydrates every payment into
     * a resource object when only the last one is wanted.
     *
     * @param object $apiOrder
     *
     * @return object|null
     */
    private static function getLastEmbeddedPayment($apiOrder)
    {
        if (!isset($apiOrder->_embedded, $apiOrder->_embedded->payments)) {
            return null;
        }

        $payments = $apiOrder->_embedded->payments;

        if (!is_array($payments) || empty($payments)) {
            return null;
        }

        $lastPayment = ArrayUtility::getLastElement($payments);

        if (!is_object($lastPayment)) {
            return null;
        }

        return $lastPayment;
    }

    /**
     * Cards, Apple Pay and Google Pay report details->failureReason, SEPA direct
     * debit reports details->bankReasonCode and point of sale reports
     * statusReason->code. None of them are declared on the SDK resources, so every
     * read is guarded.
     *
     * @param object $payment
     *
     * @return string
     */
    private static function extractReasonCode($payment)
    {
        if (isset($payment->details->failureReason)) {
            return (string) $payment->details->failureReason;
        }

        if (isset($payment->details->bankReasonCode)) {
            return (string) $payment->details->bankReasonCode;
        }

        if (isset($payment->statusReason->code)) {
            return (string) $payment->statusReason->code;
        }

        return '';
    }

    /**
     * @param string|null $status
     *
     * @return string|null
     */
    private static function terminalStatusOrNull($status)
    {
        if (!MollieStatusUtility::isPaymentFailed($status)) {
            return null;
        }

        return $status;
    }

    /**
     * @param object $resource
     * @param string $property
     *
     * @return string|null
     */
    private static function readProperty($resource, $property)
    {
        if (!isset($resource->{$property})) {
            return null;
        }

        return (string) $resource->{$property};
    }
}
