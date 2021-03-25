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

use Mollie\Config\Config;
use MolPaymentMethod;
use PrestaShop\Decimal\Number;

class PaymentFeeUtility
{
	public static function getPaymentFee(MolPaymentMethod $paymentMethod, $totalCartPrice)
	{
		switch ($paymentMethod->surcharge) {
			case Config::FEE_FIXED_FEE:
				$totalFeePrice = new Number($paymentMethod->surcharge_fixed_amount);
				break;
			case Config::FEE_PERCENTAGE:
				$totalCartPrice = new Number((string) $totalCartPrice);
				$surchargePercentage = new Number($paymentMethod->surcharge_percentage);
				$maxPercentage = new Number('100');
				$totalFeePrice = $totalCartPrice->times(
					$surchargePercentage->dividedBy(
						$maxPercentage
					)
				);
				break;
			case Config::FEE_FIXED_FEE_AND_PERCENTAGE:
				$totalCartPrice = new Number((string) $totalCartPrice);
				$surchargePercentage = new Number($paymentMethod->surcharge_percentage);
				$maxPercentage = new Number('100');
				$surchargeFixedPrice = new Number($paymentMethod->surcharge_fixed_amount);
				$totalFeePrice = $totalCartPrice->times(
					$surchargePercentage->dividedBy(
						$maxPercentage
					)
				)->plus($surchargeFixedPrice);
				break;
			case Config::FEE_NO_FEE:
			default:
				return false;
		}

		$surchargeMaxValue = new Number($paymentMethod->surcharge_limit);
		$zero = new Number('0');
		if ($surchargeMaxValue->isGreaterThan($zero) && $totalFeePrice->isGreaterOrEqualThan($surchargeMaxValue)) {
			$totalFeePrice = $surchargeMaxValue;
		}

		return $totalFeePrice->toPrecision(2);
	}
}
