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

use Mollie\Api\Resources\Order as MollieOrderAlias;
use Mollie\Api\Resources\Payment as MolliePaymentAlias;
use Mollie\DTO\PaymentFeeData;
use Mollie\Utility\NumberUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentAmountValidator
{
    const MOLLIE_AMOUNT_PRECISION = 2;

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     */
    public function validate(float $cartTotal, string $cartCurrencyIso, PaymentFeeData $paymentFeeData, $apiPayment): bool
    {
        $paymentFee = $paymentFeeData->isActive() ? $paymentFeeData->getPaymentFeeTaxIncl() : 0.0;

        $expectedAmount = NumberUtility::toPrecision(
            NumberUtility::plus($cartTotal, $paymentFee),
            self::MOLLIE_AMOUNT_PRECISION
        );

        $paidAmount = NumberUtility::toPrecision((float) $apiPayment->amount->value, self::MOLLIE_AMOUNT_PRECISION);

        if (!NumberUtility::isEqual($expectedAmount, $paidAmount)) {
            return false;
        }

        return strtoupper($cartCurrencyIso) === strtoupper((string) $apiPayment->amount->currency);
    }
}
