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

namespace Mollie\Provider;

use Address;

final class PhoneNumberProvider implements PhoneNumberProviderInterface
{
	public function getFromAddress(Address $address)
	{
		$phoneNumber = $this->getMobileOrPhone($address);

		if (empty($phoneNumber)) {
			return null;
		}
		$phoneNumber = str_replace(' ', '', $phoneNumber);
		$phoneNumber = str_replace('+', '', $phoneNumber);

		while ('0' === $phoneNumber[0]) {
			$phoneNumber = substr($phoneNumber, 1);

			if (empty($phoneNumber) && $phoneNumber !== '0') {
				return null;
			}
		}

		if ('+' !== $phoneNumber[0]) {
			$phoneNumber = '+' . $phoneNumber;
		}

		$regex = "/^\+\d{3,18}$/";
		if (1 == preg_match($regex, $phoneNumber)) {
			return $phoneNumber;
		} else {
			return null;
		}
	}

	private function getMobileOrPhone(Address $address)
	{
		return $address->phone_mobile ?: $address->phone;
	}
}
