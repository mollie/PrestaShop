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
use Mollie\Api\Types\PaymentStatus;
use Mollie\DTO\PaymentFeeData;
use Mollie\Provider\PaymentFeeProviderInterface;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderPaymentAmountValidator
{
    /** @var PaymentAmountValidator */
    private $paymentAmountValidator;

    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;

    public function __construct(PaymentAmountValidator $paymentAmountValidator, PaymentFeeProviderInterface $paymentFeeProvider)
    {
        $this->paymentAmountValidator = $paymentAmountValidator;
        $this->paymentFeeProvider = $paymentFeeProvider;
    }

    public function isApplicable(string $paymentStatus, bool $isOrderSettled, bool $hasRefundsOrChargebacks): bool
    {
        if ($isOrderSettled || $hasRefundsOrChargebacks) {
            return false;
        }

        return in_array($paymentStatus, [PaymentStatus::STATUS_PAID, PaymentStatus::STATUS_AUTHORIZED], true);
    }

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     */
    public function validate(
        $apiPayment,
        float $orderTotal,
        float $cartTotal,
        string $currencyIso,
        MolPaymentMethod $paymentMethod,
        bool $isExpressPayment
    ): bool {
        $noFee = new PaymentFeeData(0.00, 0.00, 0.00, false);

        if ($this->paymentAmountValidator->validate($orderTotal, $currencyIso, $noFee, $apiPayment)) {
            return true;
        }

        $paymentFeeData = $isExpressPayment
            ? $noFee
            : $this->paymentFeeProvider->getPaymentFee($paymentMethod, $cartTotal);

        return $this->paymentAmountValidator->validate($cartTotal, $currencyIso, $paymentFeeData, $apiPayment);
    }
}
