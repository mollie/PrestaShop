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
    public static function getRefundedAmount(array $paymentRefunds): float
    {
        $refundAmount = 0;
        foreach ($paymentRefunds as $refund) {
            if (Config::MOLLIE_REFUND_STATUS_CANCELED !== $refund->status) {
                $refundAmount = NumberUtility::plus((float) $refundAmount, (float) $refund->amount->value);
            }
        }

        return $refundAmount;
    }

    public static function getRefundableAmount(float $paymentAmount, float $refundedAmount): float
    {
        return NumberUtility::minus((float) $paymentAmount, (float) $refundedAmount);
    }
}
