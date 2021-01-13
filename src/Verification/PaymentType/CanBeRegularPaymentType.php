<?php

namespace Mollie\Verification\PaymentType;

use Mollie\Adapter\ToolsAdapter;
use Mollie\Provider\PaymentType\PaymentTypeIdentificationProvider;

class CanBeRegularPaymentType implements PaymentTypeVerificationInterface
{
	/**
	 * @var PaymentTypeIdentificationProvider
	 */
	private $regularPaymentTypeIdentification;

	/**
	 * @var ToolsAdapter
	 */
	private $toolsAdapter;

	public function __construct(
		ToolsAdapter $toolsAdapter,
		PaymentTypeIdentificationProvider $regularPaymentTypeIdentification
	) {
		$this->regularPaymentTypeIdentification = $regularPaymentTypeIdentification;
		$this->toolsAdapter = $toolsAdapter;
	}

	/**
	 * {@inheritDoc}
	 */
	public function verify($transactionId)
	{
		if (!$transactionId) {
			return false;
		}

		$regularPaymentTypeIdentification = $this->regularPaymentTypeIdentification->getRegularPaymentIdentification();

		if (!$regularPaymentTypeIdentification) {
			return false;
		}
		$length = $this->toolsAdapter->strlen($regularPaymentTypeIdentification);

		if (!$length) {
			return false;
		}

		if ($regularPaymentTypeIdentification !== $this->toolsAdapter->substr($transactionId, 0, $length)) {
			return false;
		}

		return true;
	}
}
