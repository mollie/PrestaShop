<?php

namespace Mollie\Handler\Api;

interface OrderEndpointPaymentTypeHandlerInterface
{
	/**
	 * @param string $transactionId
	 *
	 * @return int
	 */
	public function getPaymentTypeFromTransactionId($transactionId);
}
