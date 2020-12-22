<?php

namespace Mollie\Provider\PaymentType;

use MolliePrefix\Mollie\Api\Endpoints\OrderEndpoint;

class RegularPaymentTypeIdentification implements PaymentTypeIdentificationProvider
{
	/**
	 * @return string
	 */
	public function provideRegularPaymentIdentification()
	{
		return OrderEndpoint::RESOURCE_ID_PREFIX;
	}
}
