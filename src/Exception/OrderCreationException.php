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

namespace Mollie\Exception;

class OrderCreationException extends \Exception
{
	const DEFAULT_ORDER_CREATION_EXCEPTION = 1;

	const WRONG_BILLING_PHONE_NUMBER_EXCEPTION = 2;

	const WRONG_SHIPPING_PHONE_NUMBER_EXCEPTION = 3;

	const ORDER_TOTAL_LOWER_THAN_MINIMUM = 4;

	const ORDER_TOTAL_HIGHER_THAN_MAXIMUM = 5;
}
