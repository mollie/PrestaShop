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

namespace Mollie\Handler\Api;

use Mollie\Enum\PaymentTypeEnum;
use Mollie\Verification\PaymentType\PaymentTypeVerificationInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderEndpointPaymentTypeHandler implements OrderEndpointPaymentTypeHandlerInterface
{
    /**
     * @var PaymentTypeVerificationInterface
     */
    private $canBeRegularPaymentTypeVerification;

    public function __construct(PaymentTypeVerificationInterface $canBeRegularPaymentTypeVerification)
    {
        $this->canBeRegularPaymentTypeVerification = $canBeRegularPaymentTypeVerification;
    }

    /**
     * @param string $transactionId
     *
     * @return int
     */
    public function getPaymentTypeFromTransactionId($transactionId)
    {
        if ($this->canBeRegularPaymentTypeVerification->verify($transactionId)) {
            return PaymentTypeEnum::PAYMENT_TYPE_ORDER;
        }

        return PaymentTypeEnum::PAYMENT_TYPE_PAYMENT;
    }
}
