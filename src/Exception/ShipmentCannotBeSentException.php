<?php

namespace Mollie\Exception;

use Exception;

class ShipmentCannotBeSentException extends Exception
{
	const NO_SHIPPING_INFORMATION = 1;

	const AUTOMATIC_SHIPMENT_SENDER_IS_NOT_AVAILABLE = 2;

	const ORDER_HAS_NO_PAYMENT_INFORMATION = 3;

	const PAYMENT_IS_NOT_ORDER = 4;

	/**
	 * @var string
	 */
	private $orderReference;

	public function __construct($message, $code, $orderId, Exception $previous = null)
	{
		$this->orderReference = $orderId;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string
	 */
	public function getOrderReference()
	{
		return $this->orderReference;
	}
}
