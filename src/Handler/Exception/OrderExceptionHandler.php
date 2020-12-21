<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Handler\Exception;

use Exception;
use Mollie\Exception\OrderCreationException;

class OrderExceptionHandler implements ExceptionHandlerInterface
{
	/**
	 * @return OrderCreationException
	 */
	public function handle(Exception $e)
	{
		if (strpos($e->getMessage(), 'billingAddress.phone')) {
			return new OrderCreationException($e->getMessage(), OrderCreationException::WRONG_BILLING_PHONE_NUMBER_EXCEPTION);
		} elseif (strpos($e->getMessage(), 'shippingAddress.phone')) {
			return new OrderCreationException($e->getMessage(), OrderCreationException::WRONG_SHIPPING_PHONE_NUMBER_EXCEPTION);
		} elseif (strpos($e->getMessage(), 'payment.amount')) {
			if (strpos($e->getMessage(), 'minimum')) {
				throw new OrderCreationException($e->getMessage(), OrderCreationException::ORDER_TOTAL_LOWER_THAN_MINIMUM);
			}

			if (strpos($e->getMessage(), 'maximum')) {
				throw new OrderCreationException($e->getMessage(), OrderCreationException::ORDER_TOTAL_HIGHER_THAN_MAXIMUM);
			}
		}

		return new OrderCreationException($e->getMessage(), OrderCreationException::DEFAULT_ORDER_CREATION_EXCEPTION);
	}
}
