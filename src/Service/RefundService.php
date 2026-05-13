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
    public function handleRefund(string $transactionId, ?float $amount = null, ?string $orderLineId = null, ?int $quantity = null, ?int $orderId = null)
    {
        try {
            $payment = TransactionUtility::isOrderTransaction($transactionId)
                ? $this->module->getApiClient()->orders->get($transactionId, ['embed' => 'payments,refunds'])
                : $this->module->getApiClient()->payments->get($transactionId, ['embed' => 'refunds']);

            $isPartialRefund = !empty($orderLineId) || $amount !== null;

            $currency = $payment->amount->currency;

            if (TransactionUtility::isOrderTransaction($transactionId)) {
                if ($orderLineId) {
                    $lineData = ['id' => $orderLineId];
                    if ($quantity) {
                        $lineData['quantity'] = (int) $quantity;
                    }
                    $payment->refund([
                        'lines' => [$lineData],
                    ]);

                    return $this->createSuccessResponse(true, null, $currency);
                }

                $payment->refundAll();

                return $this->createSuccessResponse(false, null, $currency);
            }

            if ($orderLineId && $orderId) {
                $refundAmount = $this->refundPaymentLine($payment, (int) $orderLineId, $orderId, $quantity ?: 1);

                return $this->createSuccessResponse(true, $refundAmount, $currency);
            }

            $refundAmount = $this->calculateRefundAmount($payment, $amount);
            if (!$refundAmount) {
                $refundAmount = $payment->amount->value;
            }

            $isPartial = $amount !== null && (float) $refundAmount < (float) $payment->amount->value;

            $this->processRefund($payment, $refundAmount, false);

            return $this->createSuccessResponse($isPartial, $refundAmount, $currency);
        } catch (ApiException $e) {
            if ($e->getCode() === 409 && stripos($e->getMessage(), 'duplicate refund') !== false) {
                return $this->createErrorResponse(
                    'A matching refund was just processed on this payment. Please wait a minute and try again.',
                    $e
                );
            }

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
        $payment->refund([
            'amount' => [
                'currency' => $payment->amount->currency,
                'value' => $refundAmount,
            ],
        ]);
    }

    private function refundPaymentLine(Payment $payment, int $idOrderDetail, int $orderId, int $quantity): string
    {
        $order = new \Order($orderId);
        $unitPrice = null;
        $availableQty = 0;
        foreach ($order->getProducts() as $product) {
            if ((int) $product['id_order_detail'] === $idOrderDetail) {
                $unitPrice = (float) $product['unit_price_tax_incl'];
                $availableQty = (int) $product['product_quantity'];
                break;
            }
        }

        if ($unitPrice === null) {
            throw new \RuntimeException(sprintf('Order detail %d not found on order %d', $idOrderDetail, $orderId));
        }

        $quantity = max(1, min($quantity, $availableQty));
        $refundAmount = TextFormatUtility::formatNumber($unitPrice * $quantity, 2);

        $payment->refund([
            'amount' => [
                'currency' => $payment->amount->currency,
                'value' => $refundAmount,
            ],
            'description' => sprintf('Order #%d — line %d × %d', $orderId, $idOrderDetail, $quantity),
            'metadata' => [
                'id_order_detail' => $idOrderDetail,
                'quantity' => $quantity,
                'id_order' => $orderId,
            ],
        ]);

        return $refundAmount;
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

    private function createSuccessResponse(bool $isPartial = false, ?string $amount = null, ?string $currency = null): array
    {
        if ($isPartial) {
            $message = $amount
                ? sprintf($this->module->l('Partial refund of %s %s processed successfully.', self::FILE_NAME), $currency, $amount)
                : $this->module->l('Partial refund processed successfully.', self::FILE_NAME);
        } else {
            $message = $this->module->l('Full refund processed successfully.', self::FILE_NAME);
        }

        return [
            'success' => true,
            'msg_success' => $message,
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
