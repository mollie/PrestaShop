<?php

namespace Mollie\Provider\PaymentType;

interface PaymentTypeIdentificationProvider
{
	/**
	 * @return string
	 */
	public function getRegularPaymentIdentification();
}
