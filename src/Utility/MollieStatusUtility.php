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

namespace Mollie\Utility;

use Mollie\Api\Types\OrderStatus;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Config\Config;

class MollieStatusUtility
{
	public static function isPaymentFinished($paymentStatus)
	{
		switch ($paymentStatus) {
			case OrderStatus::STATUS_COMPLETED:
			case OrderStatus::STATUS_PAID:
			case OrderStatus::STATUS_SHIPPING:
			case PaymentStatus::STATUS_AUTHORIZED:
			case PaymentStatus::STATUS_PAID:
			case Config::STATUS_PAID_ON_BACKORDER:
				return true;
			default:
				return false;
		}
	}
}
