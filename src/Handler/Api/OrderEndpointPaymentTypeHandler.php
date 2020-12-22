<?php

namespace Mollie\Handler\Api;

use Mollie\Enum\PaymentTypeEnum;
use Mollie\Verification\PaymentType\PaymentTypeVerificationInterface;

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
	 * @return string
	 */
	public function retrievePaymentTypeFromTransactionId($transactionId)
	{
	    if ($this->canBeRegularPaymentTypeVerification->verify($transactionId)) {
            return PaymentTypeEnum::PAYMENT_TYPE_REGULAR;
        }

		return PaymentTypeEnum::PAYMENT_TYPE_NOT_FOUND;
	}
}
