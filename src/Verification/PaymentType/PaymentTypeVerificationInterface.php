<?php

namespace Mollie\Verification\PaymentType;

interface PaymentTypeVerificationInterface
{
	/**
	 * @param string $transactionId
	 *
	 * @return bool
	 */
	public function verify($transactionId);
}
