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
use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\DTO\PaymentFeeData;
use Mollie\Provider\PaymentFeeProviderInterface;
use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderPaymentAmountValidator
{
    const VALIDATED_STATUSES = [
        PaymentStatus::STATUS_PAID,
        PaymentStatus::STATUS_AUTHORIZED,
        OrderStatus::STATUS_SHIPPING,
        OrderStatus::STATUS_COMPLETED,
    ];

    /** @var PaymentAmountValidator */
    private $paymentAmountValidator;

    /** @var PaymentFeeProviderInterface */
    private $paymentFeeProvider;

    public function __construct(PaymentAmountValidator $paymentAmountValidator, PaymentFeeProviderInterface $paymentFeeProvider)
    {
        $this->paymentAmountValidator = $paymentAmountValidator;
        $this->paymentFeeProvider = $paymentFeeProvider;
    }

    public function appliesToStatus(string $paymentStatus): bool
    {
        return in_array($paymentStatus, self::VALIDATED_STATUSES, true);
    }

    /**
     * @param int[] $unpaidStateIds
     */
    public function isOrderSettled(int $currentStateId, bool $isCurrentStatePaid, int $authorizedStateId, array $unpaidStateIds): bool
    {
        if (in_array($currentStateId, $unpaidStateIds, true)) {
            return false;
        }

        if ($isCurrentStatePaid) {
            return true;
        }

        return $authorizedStateId > 0 && $currentStateId === $authorizedStateId;
    }

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     */
    public function matchesOrderTotal($apiPayment, float $orderTotal, string $currencyIso): bool
    {
        return $this->paymentAmountValidator->validate($orderTotal, $currencyIso, $this->noFee(), $apiPayment);
    }

    /**
     * @param MollieOrderAlias|MolliePaymentAlias $apiPayment
     */
    public function matchesCartTotal(
        $apiPayment,
        float $cartTotal,
        string $currencyIso,
        MolPaymentMethod $paymentMethod,
        bool $isExpressPayment
    ): bool {
        $paymentFeeData = $isExpressPayment
            ? $this->noFee()
            : $this->paymentFeeProvider->getPaymentFee($paymentMethod, $cartTotal);

        return $this->paymentAmountValidator->validate($cartTotal, $currencyIso, $paymentFeeData, $apiPayment);
    }

    private function noFee(): PaymentFeeData
    {
        return new PaymentFeeData(0.00, 0.00, 0.00, false);
    }
}
