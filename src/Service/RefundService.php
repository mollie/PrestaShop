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
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\RefundUtility;
use Mollie\Utility\TextFormatUtility;
use Mollie\Utility\TransactionUtility;
use Product;
use PrestaShopDatabaseException;
use PrestaShopException;
use Throwable;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Mollie $module, LoggerInterface $logger)
    {
        $this->module = $module;
        $this->logger = $logger;
    }

    /**
     * @param string $transactionId Transaction/Mollie Order ID
     * @param float|null $amount Amount to refund, refund all if `null`
     * @param array $orderLines Order lines for partial refund
     * @param int|null $productId Specific product ID for partial refund
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ApiException
     *
     * @since 3.3.0 Renamed `doRefund` to `doPaymentRefund`, added `$amount`
     * @since 3.3.2 Omit $orderId
     * @since 3.3.3 Added partial refund support for specific products
     */
    public function handleRefund(string $transactionId, ?float $amount = null, array $orderLines = [], bool $isPartial = false)
    {
        try {
            $payment = $this->getPayment($transactionId);
            $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

            if ($isPartial && $isOrderTransaction) {
                $this->processPartialRefund($payment, $amount);

                return $this->createSuccessResponse();
            }

            $refundAmount = $this->calculateRefundAmount($payment, $amount);

            if (!$refundAmount) {
                return $this->createErrorResponse('No refundable amount available.', null);
            }

            $this->processRefund($payment, $refundAmount, $isOrderTransaction);

            return $this->createSuccessResponse();
        } catch (ApiException $e) {
            return $this->createErrorResponse('The order could not be refunded!', $e);
        } catch (Throwable $e) {
            return $this->createErrorResponse('Something went wrong while processing the refund.', $e);
        }
    }

    /**
     * @param string $transactionId
     *
     * @return MollieOrderAlias|Payment
     *
     * @throws ApiException
     */
    private function getPayment(string $transactionId)
    {
        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

        if ($isOrderTransaction) {
            return $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments']);
        }

        return $this->module->getApiClient()->payments->get($transactionId);
    }

    /**
     * @param MollieOrderAlias|Payment $payment
     * @param float|null $amount
     * @return string|null
     */
    private function calculateRefundAmount($payment, ?float $amount): ?string
    {
        if ($amount) {
            return TextFormatUtility::formatNumber($amount, 2);
        }

        $settlementAmount = (float) $payment->settlementAmount->value;
        $refundedAmount = (float) RefundUtility::getRefundedAmount(iterator_to_array($payment->refunds()));
        $refundableAmount = RefundUtility::getRefundableAmount($settlementAmount, $refundedAmount);

        if ($refundableAmount <= 0) {
            return null;
        }

        return TextFormatUtility::formatNumber($refundableAmount, 2);
    }


    /**
     * @param MollieOrderAlias|Payment $payment
     * @param string $refundAmount
     * @param bool $isOrderTransaction
     * @throws ApiException
     */
    private function processRefund($payment, string $refundAmount, bool $isOrderTransaction): void
    {
        if ($isOrderTransaction) {
            $this->refundOrder($payment, $refundAmount);
            return;
        }

        $payment->refund([
            'amount' => [
                'currency' => $payment->amount->currency,
                'value' => $refundAmount,
            ],
        ]);
    }

    /**
     * @param MollieOrderAlias $order
     * @param array $orderLines
     * @param int $productId
     * @return array
     * @throws ApiException
     */
    private function processPartialRefund(MollieOrderAlias $order, float $amount): array
    {
        $order->refund([
            'lines' => RefundUtility::getRefundLines($order->lines),
        ]);

        return $this->createSuccessResponse();
    }

    /**
     * @param string $message
     * @param Throwable|null $e
     *
     * @return array
     */
    private function createErrorResponse(string $message, ?Throwable $e = null): array
    {
        $this->logger->error(sprintf('%s - Error while processing the refund.', self::FILE_NAME), [
            'exceptions' => ExceptionUtility::getExceptions($e),
        ]);

        $response = [
            'success' => false,
            'message' => $this->module->l($message, self::FILE_NAME),
            'error' => $e ? $e->getMessage() : 'NaN',
        ];

        return $response;
    }

    private function refundOrder(MollieOrderAlias $order, string $refundAmount): void
    {
        $order->refundAll();
    }

    /**
     * @return array
     */
    private function createSuccessResponse(): array
    {
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

    public function getRefundedAmount(string $transactionId): float
    {
        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

        $transaction = $isOrderTransaction
            ? $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments'])
            : $this->module->getApiClient()->payments->get($transactionId);

        return (float) RefundUtility::getRefundedAmount(iterator_to_array($transaction->refunds()));
    }

    public function isRefunded(string $transactionId, float $amount): bool
    {
        $isOrderTransaction = TransactionUtility::isOrderTransaction($transactionId);

        $transaction = $isOrderTransaction
            ? $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments'])
            : $this->module->getApiClient()->payments->get($transactionId);

        $refundedAmount = (float) RefundUtility::getRefundedAmount(iterator_to_array($transaction->refunds()));
        $refundedAmount2 = $transaction->amountRefunded ? (float) $transaction->amountRefunded->value : null;

        if ($refundedAmount2) {
            return $refundedAmount >= $amount || (float) $transaction->amountRefunded->value >= $amount;
        }

        return $refundedAmount >= $amount;
    }
}
