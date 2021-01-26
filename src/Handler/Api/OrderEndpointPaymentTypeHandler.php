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
