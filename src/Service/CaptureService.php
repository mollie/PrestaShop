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
use Mollie\Utility\TextFormatUtility;
use Mollie\Api\Resources\Payment;
use Mollie\Utility\TransactionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CaptureService
{
    const FILE_NAME = 'CaptureService';

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * Capture a payment by transaction ID (Payments API)
     *
     * @param string $transactionId
     * @param float|null $amount
     *
     * @return array
     */
    public function handleCapture($transactionId, $amount = null)
    {
        try {
            $payment = $this->getPayment($transactionId);
            $this->performCapture($transactionId, $payment, $amount);

            return $this->createSuccessResponse();
        } catch (\Throwable $e) {
            return $this->createErrorResponse($e);
        }
    }

    /**
     * Get payment from Mollie API
     *
     * @param string $transactionId
     *
     * @return Payment
     */
    private function getPayment(string $transactionId): Payment
    {
        return $this->module->getApiClient()->payments->get($transactionId);
    }

    /**
     * Perform the actual capture operation
     *
     * @param string $transactionId
     * @param Payment $payment
     * @param float|null $amount
     */
    private function performCapture(string $transactionId, Payment $payment, ?float $amount): void
    {
        if ($amount) {
            $this->capturePartialAmount($transactionId, $payment, $amount);
        } else {
            $this->captureFullAmount($transactionId);
        }
    }

    /**
     * Capture a partial amount
     *
     * @param string $transactionId
     * @param Payment $payment
     * @param float $amount
     */
    private function capturePartialAmount(string $transactionId, Payment $payment, float $amount): void
    {
        $captureData = [
            'amount' => [
                'currency' => $payment->amount->currency,
                'value' => TextFormatUtility::formatNumber($amount, 2, '.', ''),
            ],
        ];

        $this->module->getApiClient()->paymentCaptures->createForId($transactionId, $captureData);
    }

    /**
     * Capture the full amount
     *
     * @param string $transactionId
     */
    private function captureFullAmount(string $transactionId): void
    {
        $this->module->getApiClient()->paymentCaptures->createForId($transactionId);
    }

    /**
     * Create success response
     *
     * @return array
     */
    private function createSuccessResponse(): array
    {
        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

    /**
     * Create error response
     *
     * @param \Throwable $exception
     *
     * @return array
     */
    private function createErrorResponse(\Throwable $exception): array
    {
        return [
            'success' => false,
            'message' => $this->module->l('The payment could not be captured!', self::FILE_NAME),
            'detailed' => $exception->getMessage(),
        ];
    }

    /**
     * Check if a payment is captured. Only applicable for payments API.
     *
     * @param string $transactionId
     *
     * @return bool
     */
    public function isCaptured(string $transactionId): bool
    {
        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

        if ($isOrderTransaction) {
            return false;
        }

        /** @var Payment $payment */
        $payment = $this->module->getApiClient()->payments->get($transactionId);

        $captures = $payment->captures();

        $capturedAmount = 0;

        foreach ($captures as $capture) {
            $capturedAmount += $capture->amount->value;
        }

        return $capturedAmount >= $payment->amount->value;
    }

    public function getCapturableAmount(string $transactionId): float
    {
        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

        if ($isOrderTransaction) {
            return 0.0;
        }

        /** @var Payment $payment */
        $payment = $this->module->getApiClient()->payments->get($transactionId);

        // If payment is already captured
        if ($payment->status == 'paid' || !isset($payment->_links->captures)) {
            return 0.0;
        }

        $captures = $payment->captures();

        $capturedAmount = 0.00;

        foreach ($captures as $capture) {
            $capturedAmount += $capture->amount->value;
        }

        return $payment->amount->value - $capturedAmount;
    }
}
