<?php

namespace Mollie\Verification\OrderTotal;

use Mollie\Exception\OrderTotalRestrictionException;

interface OrderTotalVerificationInterface
{
	/**
	 * @return bool
	 *
	 * @throws OrderTotalRestrictionException
	 */
	public function verify();
}
