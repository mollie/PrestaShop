<?php

namespace Mollie\Provider\PaymentType;

use Mollie\Api\Endpoints\OrderEndpoint;

class RegularPaymentTypeIdentification implements PaymentTypeIdentificationProvider
{
	/**
	 * @return string
	 */
	public function getRegularPaymentIdentification()
	{
		return OrderEndpoint::RESOURCE_ID_PREFIX;
	}
}
