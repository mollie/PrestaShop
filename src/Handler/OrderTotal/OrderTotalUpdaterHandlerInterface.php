<?php

namespace Mollie\Handler\OrderTotal;

use Mollie\Exception\OrderTotalRestrictionException;

interface OrderTotalUpdaterHandlerInterface
{
	/**
	 * @return bool
	 *
	 * @throws OrderTotalRestrictionException
	 */
	public function handleOrderTotalUpdate();
}
