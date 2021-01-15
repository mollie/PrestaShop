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

use Address;
use Cart;
use Configuration;
use Country;
use Customer;

class TextGeneratorUtility
{
	/**
	 * Generate a description from the Cart.
	 *
	 * @param string $methodDescription
	 * @param Cart|int $cartId Cart or Cart ID
	 * @param string $orderReference Order reference
	 *
	 * @return string Description
	 *
	 * @since 3.0.0
	 */
	public static function generateDescriptionFromCart($methodDescription, $cartId, $orderReference)
	{
		if ($cartId instanceof Cart) {
			$cart = $cartId;
		} else {
			$cart = new Cart($cartId);
		}
		$buyer = null;
		if ($cart->id_customer) {
			$buyer = new Customer((int) $cart->id_customer);
		}

		$countryCode = '';
		if ($cart->id_address_delivery) {
			$deliveryAddress = new Address((int) $cart->id_address_delivery);
			$countryId = $deliveryAddress->id_country;
			$country = new Country($countryId);
			$countryCode = $country->iso_code;
		}
		$filters = [
			'%' => $cartId,
			'{cart.id}' => $cartId,
			'{order.reference}' => $orderReference,
			'{customer.firstname}' => null == $buyer ? '' : $buyer->firstname,
			'{customer.lastname}' => null == $buyer ? '' : $buyer->lastname,
			'{customer.company}' => null == $buyer ? '' : $buyer->company,
			'{storeName}' => Configuration::get('PS_SHOP_NAME'),
			'{orderNumber}' => $orderReference,
			'{countryCode}' => $countryCode,
		];

		$content = str_ireplace(
			array_keys($filters),
			array_values($filters),
			$methodDescription
		);

		$description = empty($content) ? $orderReference : $content;

		return $description;
	}
}
