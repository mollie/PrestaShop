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
use Mollie\Logger\LoggerInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Utility\RefundUtility;
use Mollie\Utility\TextFormatUtility;
use Mollie\Utility\TransactionUtility;
use Throwable;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RefundService
{
    const FILE_NAME = 'RefundService';

    /** @var Mollie */
    private $module;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Mollie $module, LoggerInterface $logger)
    {
        $this->module = $module;
        $this->logger = $logger;
    }

    /**
     * @param string $transactionId Transaction/Mollie Order ID
     * @param float|null $amount Amount to refund, refund all if `null`
     * @param string|null $orderLineId Order line ID for partial refund
     *
     * @return array
     */
    public function handleRefund(string $transactionId, ?float $amount = null, ?string $orderLineId = null)
    {
        try {
            $payment = TransactionUtility::isOrderTransaction($transactionId)
                ? $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments,refunds'])
                : $this->module->getApiClient()->payments->get($transactionId, ['embed' => 'refunds']);

            $isPartialRefund = !empty($orderLineId) || $amount !== null;

            if ($isPartialRefund && TransactionUtility::isOrderTransaction($transactionId)) {
                $payment->refund([
                    'lines' => [
                        ['id' => $orderLineId],
                    ],
                ]);

                return $this->createSuccessResponse();
            }

            $refundAmount = $this->calculateRefundAmount($payment, $amount);
            if (!$refundAmount) {
                $refundAmount = $payment->amount->value;
            }

            $this->processRefund($payment, $refundAmount, TransactionUtility::isOrderTransaction($transactionId));

            return $this->createSuccessResponse();
        } catch (ApiException $e) {
            return $this->createErrorResponse('The order could not be refunded!', $e);
        } catch (Throwable $e) {
            return $this->createErrorResponse('Something went wrong while processing the refund.', $e);
        }
    }

    /**
     * @param MollieOrderAlias|Payment $payment
     * @param float|null $amount
     *
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

        return $refundableAmount > 0 ? TextFormatUtility::formatNumber($refundableAmount, 2) : null;
    }

    /**
     * @param MollieOrderAlias|Payment $payment
     * @param string $refundAmount
     * @param bool $isOrderTransaction
     *
     * @throws ApiException
     */
    private function processRefund($payment, string $refundAmount, bool $isOrderTransaction): void
    {
        if ($isOrderTransaction) {
            $payment->refundAll();

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

        return [
            'success' => false,
            'message' => $this->module->l($message, self::FILE_NAME),
            'error' => $e ? $e->getMessage() : 'NaN',
        ];
    }

    private function createSuccessResponse(): array
    {
        return [
            'success' => true,
            'msg_success' => $this->module->l('The order has been refunded!', self::FILE_NAME),
            'msg_details' => $this->module->l('Mollie will transfer the amount back to the customer on the next business day.', self::FILE_NAME),
        ];
    }

    public function isRefunded(string $transactionId, float $amount): bool
    {
        $transaction = TransactionUtility::isOrderTransaction($transactionId)
            ? $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments,refunds'])
            : $this->module->getApiClient()->payments->get($transactionId, ['embed' => 'refunds']);

        $refundedAmount = $transaction->amountRefunded
            ? (float) $transaction->amountRefunded->value
            : RefundUtility::getRefundedAmount(iterator_to_array($transaction->refunds()));

        return $refundedAmount >= $amount;
    }
}
