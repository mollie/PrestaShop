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
    public function doPaymentRefund($transactionId, $amount = null)
    {
        try {
            /** @var Payment $payment */
            $payment = $this->module->getApiClient()->payments->get($transactionId);
            if ($amount) {
                $payment->refund([
                    'amount' => [
                        'currency' => (string) $payment->amount->currency,
                        'value' => (string) TextFormatUtility::formatNumber($amount, 2),
                    ],
                ]);
            } elseif ((float) $payment->settlementAmount->value - (float) $payment->amountRefunded->value > 0) {
                $payment->refund([
                    'amount' => [
                        'currency' => (string) $payment->amount->currency,
                        'value' => (string) TextFormatUtility::formatNumber(
                            RefundUtility::getRefundableAmount(
                                (float) $payment->settlementAmount->value,
                                (float) RefundUtility::getRefundedAmount(iterator_to_array($payment->refunds()))
                            ),
                            2
                        ),
                    ],
                ]);
            }
        } catch (ApiException $e) {
            return [
                'status' => 'fail',
                'msg_fail' => $this->module->l('The order could not be refunded!', self::FILE_NAME),
                'msg_details' => $this->module->l('Reason:', self::FILE_NAME) . ' ' . $e->getMessage(),
            ];
        }

        return [
            'status' => 'success',
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
                $refund = RefundUtility::getRefundLines($lines);
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
}
