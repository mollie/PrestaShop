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
use Mollie\Utility\TransactionUtility;
use PrestaShopDatabaseException;
use PrestaShopException;

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
     * @param string $transactionId Transaction/Mollie Order ID
     * @param float|null $amount Amount to refund, refund all if `null`
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
    public function doPaymentRefund(string $transactionId, float $amount = null, array $orderLines = [])
    {
        try {
            $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

            if ($isOrderTransaction) {
                /** @var MollieOrderAlias $payment */
                $payment = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
            } else {
                /** @var Payment $payment */
                $payment = $this->module->getApiClient()->payments->get($transactionId);
            }

            if ($amount) {
                $refundAmount = TextFormatUtility::formatNumber($amount, 2);
            } else {
                $settlementAmount = (float) $payment->settlementAmount->value;
                $refundedAmount = (float) RefundUtility::getRefundedAmount(iterator_to_array($payment->refunds()));
                $refundableAmount = RefundUtility::getRefundableAmount($settlementAmount, $refundedAmount);

                if ($refundableAmount <= 0) {
                    return [
                        'success' => false,
                        'message' => $this->module->l('No refundable amount available.', self::FILE_NAME),
                    ];
                }

                $refundAmount = TextFormatUtility::formatNumber($refundableAmount, 2);
            }

            if (isset($refundAmount) && (float) $refundAmount > 0) {
                $payment->refundAll();
            }
        } catch (ApiException $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The order could not be refunded!', self::FILE_NAME),
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'msg_success' => $this->module->l('The order has been refunded!', self::FILE_NAME),
            'msg_details' => $this->module->l('Mollie will transfer the amount back to the customer on the next business day.', self::FILE_NAME),
        ];
    }

    /**
     * @param array $lines
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 3.3.0
     */
    public function doRefundOrderLines(array $orderData, $lines = [])
    {
        $transactionId = $orderData['id'];
        $availableRefund = $orderData['availableRefundAmount'];
        try {
            /** @var MollieOrderAlias $payment */
            $order = $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
            $isOrderLinesRefundPossible = RefundUtility::isOrderLinesRefundPossible($lines, $availableRefund);
            if ($isOrderLinesRefundPossible) {
                $refund = RefundUtility::getRefundLines($lines, $transactionId);
                $order->refund($refund);
            } else {
                /** @var PaymentCollection $orderPayments */
                $orderPayments = $order->payments();
                /** @var \Mollie\Api\Resources\Payment $orderPayment */
                foreach ($orderPayments as $orderPayment) {
                    $orderPayment->refund(
                        [
                            'amount' => $availableRefund,
                        ]
                    );
                    continue;
                }
            }
        } catch (ApiException $e) {
            return [
                'success' => false,
                'message' => $this->module->l('The product(s) could not be refunded!', self::FILE_NAME),
                'detailed' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => '',
            'detailed' => '',
        ];
    }

    public function isRefunded(string $transactionId, float $amount): bool
    {
        $transaction = TransactionUtility::isOrderTransaction($transactionId)
            ? $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments'])
            : $this->module->getApiClient()->payments->get($transactionId);

        $refundedAmount = (float) RefundUtility::getRefundedAmount(iterator_to_array($transaction->refunds()));

        return $refundedAmount >= $amount || $transaction->amountRefunded->value >= $amount;
    }
}
