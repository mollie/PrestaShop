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

namespace Mollie\Service;

use Mollie;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Utility\RefundUtility;
use Mollie\Utility\TextFormatUtility;
use Mollie\Utility\NumberUtility;
use PrestaShopDatabaseException;
use PrestaShopException;
use Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RefundService
{
    const FILE_NAME = 'RefundService';

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * Process payment refund with comprehensive data validation
     *
     * @param string $transactionId Transaction/Mollie Payment ID
     * @param float|null $amount Amount to refund, refund all if `null`
     * @param array $refundData Additional refund data (optional)
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ApiException
     *
     * @since 3.3.0 Renamed `doRefund` to `doPaymentRefund`, added `$amount`
     * @since 3.3.2 Omit $orderId
     */
    public function doPaymentRefund($transactionId, $amount = null, array $refundData = [])
    {
        if (empty($transactionId)) {
            return $this->createErrorResponse('Invalid transaction ID provided');
        }

        try {
            /** @var Payment $payment */
            $payment = $this->module->getApiClient()->payments->get($transactionId);

            if (!$this->isPaymentRefundable($payment)) {
                return $this->createErrorResponse('Payment is not refundable');
            }

            $refundPayload = $this->buildPaymentRefundPayload($payment, $amount, $refundData);

            if (empty($refundPayload)) {
                return $this->createErrorResponse('No refundable amount available');
            }

            $payment->refund($refundPayload);

            return $this->createSuccessResponse(
                'The payment has been refunded!',
                'Mollie will transfer the amount back to the customer on the next business day.'
            );

        } catch (ApiException $e) {
            return $this->createErrorResponse(
                'The payment could not be refunded!',
                'Reason: ' . $e->getMessage()
            );
        }
    }

    /**
     * Process order line refunds with comprehensive data validation
     *
     * @param array $orderData Order data containing id and availableRefundAmount
     * @param array $lines Order lines to refund
     * @param array $refundData Additional refund data (optional)
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function doRefundOrderLines(array $orderData, array $lines = [], array $refundData = [])
    {
        if (!$this->validateOrderData($orderData)) {
            return $this->createErrorResponse('Invalid order data provided');
        }

        $transactionId = $orderData['id'];
        $availableRefund = $orderData['availableRefundAmount'];

        try {
            /** @var MollieOrderAlias $order */
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);

            if (!$this->isOrderRefundable($order)) {
                return $this->createErrorResponse('Order is not refundable');
            }

            $order->refundAll();

            return $this->createSuccessResponse(
                'The product(s) have been refunded!',
                'Mollie will process the refund for the selected items.'
            );
        } catch (ApiException $e) {
            return $this->createErrorResponse(
                'The product(s) could not be refunded!',
                $e->getMessage()
            );
        }
    }

    /**
     * Process refund with automatic detection of payment vs order
     *
     * @param string $transactionId Transaction ID (Payment or Order)
     * @param array $refundOptions Refund options
     *
     * @return array
     */
    public function processRefund(string $transactionId, array $refundOptions = [])
    {
        return $this->doRefundOrderLines($refundOptions);
    }

    /**
     * Check if payment is refundable
     *
     * @param Payment $payment
     * @return bool
     */
    private function isPaymentRefundable(Payment $payment): bool
    {
        return in_array($payment->status, ['paid', 'authorized']) &&
               (float) $payment->settlementAmount->value > 0;
    }

    /**
     * Check if order is refundable
     *
     * @param MollieOrderAlias $order
     * @return bool
     */
    private function isOrderRefundable(MollieOrderAlias $order): bool
    {
        return in_array($order->status, ['paid', 'authorized']) &&
               (float) $order->amount->value > 0;
    }

    /**
     * Build payment refund payload
     *
     * @param Payment $payment
     * @param float|null $amount
     * @param array $refundData
     * @return array|null
     */
    private function buildPaymentRefundPayload(Payment $payment, $amount = null, array $refundData = []): ?array
    {
        $currency = (string) $payment->amount->currency;

        if ($amount !== null) {
            $refundAmount = $this->validateRefundAmount($amount, $payment);
            if ($refundAmount === null) {
                return null;
            }
        } else {
            $refundAmount = $this->calculateFullRefundAmount($payment);
            if ($refundAmount <= 0) {
                return null;
            }
        }

        $payload = [
            'amount' => [
                'currency' => $currency,
                'value' => (string) TextFormatUtility::formatNumber($refundAmount, 2),
            ],
        ];

        if (!empty($refundData['description'])) {
            $payload['description'] = $refundData['description'];
        }

        if (!empty($refundData['metadata'])) {
            $payload['metadata'] = $refundData['metadata'];
        }

        return $payload;
    }

    /**
     * Validate refund amount against payment
     *
     * @param float $amount
     * @param Payment $payment
     * @return float|null
     */
    private function validateRefundAmount(float $amount, Payment $payment): ?float
    {
        $maxRefundable = $this->calculateMaxRefundableAmount($payment);

        if ($amount <= 0 || $amount > $maxRefundable) {
            return null;
        }

        return $amount;
    }

    /**
     * Calculate maximum refundable amount
     *
     * @param Payment $payment
     * @return float
     */
    private function calculateMaxRefundableAmount(Payment $payment): float
    {
        $settlementAmount = (float) $payment->settlementAmount->value;
        $refundedAmount = (float) RefundUtility::getRefundedAmount(iterator_to_array($payment->refunds()));

        return RefundUtility::getRefundableAmount($settlementAmount, $refundedAmount);
    }

    /**
     * Calculate full refund amount
     *
     * @param Payment $payment
     * @return float
     */
    private function calculateFullRefundAmount(Payment $payment): float
    {
        $settlementAmount = (float) $payment->settlementAmount->value;
        $refundedAmount = (float) RefundUtility::getRefundedAmount(iterator_to_array($payment->refunds()));

        return RefundUtility::getRefundableAmount($settlementAmount, $refundedAmount);
    }

    /**
     * Validate order data
     *
     * @param array $orderData
     * @return bool
     */
    private function validateOrderData(array $orderData): bool
    {
        return !empty($orderData['id']) &&
               isset($orderData['availableRefundAmount']) &&
               $orderData['availableRefundAmount'] > 0;
    }

    /**
     * Check if order lines refund is possible
     *
     * @param array $lines
     * @param array $availableRefund
     * @return bool
     */
    private function shouldRefundOrderLines(array $lines): bool
    {
        return !empty($lines);
    }

    /**
     * Process order lines refund
     *
     * @param MollieOrderAlias $order
     * @param array $lines
     * @param array $refundData
     * @return array
     */
    private function processOrderLinesRefund(MollieOrderAlias $order, array $lines, array $refundData): array
    {
        $refundPayload = RefundUtility::getRefundLines($lines);

        if (!empty($refundData['description'])) {
            $refundPayload['description'] = $refundData['description'];
        }

        if (!empty($refundData['metadata'])) {
            $refundPayload['metadata'] = $refundData['metadata'];
        }

        $order->refund($refundPayload);

        return $this->createSuccessResponse(
            'The product(s) have been refunded!',
            'Mollie will process the refund for the selected items.'
        );
    }

    /**
     * Process full order refund
     *
     * @param MollieOrderAlias $order
     * @param array $availableRefund
     * @param array $refundData
     * @return array
     */
    private function processFullOrderRefund(MollieOrderAlias $order, array $availableRefund, array $refundData): array
    {
        /** @var PaymentCollection $orderPayments */
        $orderPayments = $order->payments();

        foreach ($orderPayments as $orderPayment) {
            $refundPayload = [
                'amount' => $availableRefund,
            ];

            if (!empty($refundData['description'])) {
                $refundPayload['description'] = $refundData['description'];
            }

            if (!empty($refundData['metadata'])) {
                $refundPayload['metadata'] = $refundData['metadata'];
            }

            $orderPayment->refund($refundPayload);
        }

        return $this->createSuccessResponse(
            'The order has been fully refunded!',
            'Mollie will transfer the amount back to the customer on the next business day.'
        );
    }

    /**
     * Check if transaction ID is for an order
     *
     * @param string $transactionId
     * @return bool
     */
    private function isOrderTransaction(string $transactionId): bool
    {
        return strpos($transactionId, 'ord_') === 0;
    }

    /**
     * Create success response
     *
     * @param string $message
     * @param string $details
     * @return array
     */
    private function createSuccessResponse(string $message, string $details = ''): array
    {
        return [
            'status' => 'success',
            'success' => true,
            'msg_success' => $this->module->l($message, self::FILE_NAME),
            'message' => $this->module->l($message, self::FILE_NAME),
            'msg_details' => $this->module->l($details, self::FILE_NAME),
            'detailed' => $this->module->l($details, self::FILE_NAME),
        ];
    }

    /**
     * Create error response
     *
     * @param string $message
     * @param string $details
     * @return array
     */
    private function createErrorResponse(string $message, string $details = ''): array
    {
        return [
            'status' => 'fail',
            'success' => false,
            'msg_fail' => $this->module->l($message, self::FILE_NAME),
            'message' => $this->module->l($message, self::FILE_NAME),
            'msg_details' => $this->module->l($details, self::FILE_NAME),
            'detailed' => $this->module->l($details, self::FILE_NAME),
        ];
    }
}
