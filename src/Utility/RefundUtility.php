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
use Product;

if (!defined('_PS_VERSION_')) {
    exit;
}

class RefundUtility
{
    public static function getRefundLines(array $lines, ?int $idProduct = null)
    {
        $refunds = [];

        foreach ($lines as $line) {
            if ($idProduct == $line->id) {
            $refunds[] = [
                    'id' => $line->id,
                ];
            }
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
