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
    public function doPaymentCapture($transactionId, $amount = null)
    {
        try {
            $payment = $this->module->getApiClient()->payments->get($transactionId);

            if ($amount !== null && !empty($amount)) {
                $captureData = [
                    'amount' => [
                        'currency' => $payment->amount->currency,
                        'value' => TextFormatUtility::formatNumber($amount, 2, '.', ''),
                    ],
                ];
                $capture = $this->module->getApiClient()->paymentCaptures->createForId($transactionId, $captureData);
            } else {
                $capture = $this->module->getApiClient()->paymentCaptures->createForId($transactionId);
            }

            return [
                'success' => true,
                'message' => '',
                'detailed' => '',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The payment could not be captured!', self::FILE_NAME),
                'detailed' => $e->getMessage(),
            ];
        }
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

        $payment = $this->module->getApiClient()->payments->get($transactionId);

        $status = $payment->status;
        $amount = $payment->amount;

        return $status === 'paid' || $amount->value <= $amount->settlementAmount->value;
    }
}
