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

namespace Mollie\Service;

use Mollie\Utility\PaymentFeeUtility;
use MolPaymentMethod;
use Tools;

class OrderFeeService
{
	public function getPaymentFees($methods, $totalPrice)
	{
		foreach ($methods as $index => $method) {
			if (0 === (int) $method['surcharge']) {
				$methods[$index]['fee'] = false;
				$methods[$index]['fee_display'] = false;
				continue;
			}
			$paymentMethod = new MolPaymentMethod($method['id_payment_method']);
			$paymentFee = PaymentFeeUtility::getPaymentFee($paymentMethod, $totalPrice);
			$methods[$index]['fee'] = $paymentFee;
			$methods[$index]['fee_display'] = Tools::displayPrice($paymentFee);
		}

		return $methods;
	}
}
