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

if (!defined('_PS_VERSION_')) {
    exit;
}

class RefundUtility
{
    public static function getRefundLines(array $lines)
    {
        $refunds = [];
        foreach ($lines as $line) {
            $refund = array_intersect_key(
                (array) $line,
                array_flip([
                    'id',
                    'quantity',
                ]));
            $refunds['lines'][] = $refund;
        }

        return $refunds;
    }

    public static function isOrderLinesRefundPossible(array $lines, $availableRefund)
    {
        $refundedAmount = 0;
        foreach ($lines as $line) {
            $lineRefundAmount = NumberUtility::times($line['unitPrice']['value'], $line['quantity']);
            $refundedAmount = NumberUtility::plus($refundedAmount, $lineRefundAmount);
        }

        return NumberUtility::isLowerOrEqualThan($refundedAmount, $availableRefund['value']);
    }

    public static function getRefundedAmount($paymentRefunds)
    {
        $refundAmount = 0;
        foreach ($paymentRefunds as $refund) {
            if (Config::MOLLIE_REFUND_STATUS_CANCELED !== $refund->status) {
                $refundAmount = NumberUtility::plus((float) $refundAmount, (float) $refund->amount->value);
            }
        }

        return $refundAmount;
    }

    public static function getRefundableAmount($paymentAmount, $refundedAmount)
    {
        return NumberUtility::minus((float) $paymentAmount, (float) $refundedAmount);
    }
}
