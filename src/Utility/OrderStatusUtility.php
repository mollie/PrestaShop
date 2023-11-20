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

use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\Api\Resources\PaymentCollection;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Api\Types\RefundStatus;
use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderStatusUtility
{
    /**
     * @param string $status
     * @param string $comparedStatus
     *
     * @return string
     */
    public static function transformPaymentStatusToPaid($status, $comparedStatus)
    {
        if ($status === $comparedStatus) {
            return PaymentStatus::STATUS_PAID;
        }

        return $status;
    }

    /**
     * @param MolliePaymentAlias|MollieOrderAlias $transaction
     */
    public static function transformPaymentStatusToRefunded($transaction)
    {
        if (null === $transaction->amountRefunded ||
            null === $transaction->amountCaptured) {
            return $transaction->status;
        }

        $isVoucher = Config::MOLLIE_VOUCHER_METHOD_ID === $transaction->method;
        $remainingAmount = 0;
        if ($isVoucher) {
            /** @var PaymentCollection $payments */
            $payments = $transaction->payments();
            /** @var MolliePaymentAlias $payment */
            foreach ($payments as $payment) {
                $remainingAmount = $payment->getAmountRemaining();
            }
        }
        $amountRefunded = (float) $transaction->amountRefunded->value;
        $amountPaid = (float) $transaction->amountCaptured->value;
        $isPartiallyRefunded = NumberUtility::isLowerThan($amountRefunded, $amountPaid);
        $isFullyRefunded = NumberUtility::isEqual($amountRefunded, $amountPaid);

        if ($isPartiallyRefunded) {
            if ($isVoucher && NumberUtility::isEqual(0, $remainingAmount)) {
                return RefundStatus::STATUS_REFUNDED;
            }

            return Config::PARTIAL_REFUND_CODE;
        }

        if ($isFullyRefunded) {
            return RefundStatus::STATUS_REFUNDED;
        }

        return $transaction->status;
    }
}
