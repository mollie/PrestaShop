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

namespace Mollie\Validator;

use Mollie\Config\Config;
use Mollie\DTO\PaymentFeeData;
use Mollie\Exception\InvalidPaymentFeePercentageException;
use Mollie\Exception\PaymentFeeExceedsCartAmountException;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentFeeValidator
{
    private const MAX_NEGATIVE_PERCENTAGE = -100;
    private const MAX_POSITIVE_PERCENTAGE = 100;

    public function validatePaymentFeePercentage(MolPaymentMethod $paymentMethod): void
    {
        if ($paymentMethod->surcharge == Config::FEE_PERCENTAGE ||
            $paymentMethod->surcharge == Config::FEE_FIXED_FEE_AND_PERCENTAGE) {
            $percentage = (float) $paymentMethod->surcharge_percentage;

            if ($percentage <= self::MAX_NEGATIVE_PERCENTAGE) {
                throw new InvalidPaymentFeePercentageException(sprintf('Payment fee percentage cannot be less than %d%%. Current value: %.2f%%', self::MAX_NEGATIVE_PERCENTAGE, $percentage));
            }

            if ($percentage >= self::MAX_POSITIVE_PERCENTAGE) {
                throw new InvalidPaymentFeePercentageException(sprintf('Payment fee percentage cannot be greater than %d%%. Current value: %.2f%%', self::MAX_POSITIVE_PERCENTAGE, $percentage));
            }
        }
    }

    public function validatePaymentFeeAmount(PaymentFeeData $paymentFeeData, float $cartAmount): void
    {
        $paymentFeeAmount = $paymentFeeData->getPaymentFeeTaxIncl();

        if ($paymentFeeAmount < 0 && abs($paymentFeeAmount) >= $cartAmount) {
            throw new PaymentFeeExceedsCartAmountException(sprintf('Negative payment fee amount (%.2f) cannot exceed cart amount (%.2f)', abs($paymentFeeAmount), $cartAmount));
        }
    }
}
