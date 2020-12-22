<?php

namespace Mollie\Handler\Api;

interface OrderEndpointPaymentTypeHandlerInterface
{
	/**
	 * @param string $transactionId
	 *
	 * @return string
	 */
	public function retrievePaymentTypeFromTransactionId($transactionId);
}
