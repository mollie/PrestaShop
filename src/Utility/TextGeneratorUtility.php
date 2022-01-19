<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Address;
use Cart;
use Configuration;
use Country;
use Customer;
use Order;

class TextGeneratorUtility
{
    /**
     * Generate a description from the Cart.
     *
     * @param string $methodDescription
     * @param int $orderId
     *
     * @return string Description
     */
    public static function generateDescriptionFromCart($methodDescription, $orderId)
    {
        $order = new Order($orderId);
        $cart = Cart::getCartByOrderId($orderId);
        $buyer = null;
        if ($cart && $cart->id_customer) {
            $buyer = new Customer($cart->id_customer);
        }

        $countryCode = '';
        if ($cart->id_address_delivery) {
            $deliveryAddress = new Address(($cart->id_address_delivery));
            $countryId = $deliveryAddress->id_country;
            $country = new Country($countryId);
            $countryCode = $country->iso_code;
        }
        $filters = [
            '%' => $cart->id,
            '{cart.id}' => $cart->id,
            '{order.reference}' => $order->reference,
            '{customer.firstname}' => null == $buyer ? '' : $buyer->firstname,
            '{customer.lastname}' => null == $buyer ? '' : $buyer->lastname,
            '{customer.company}' => null == $buyer ? '' : $buyer->company,
            '{storeName}' => Configuration::get('PS_SHOP_NAME'),
            '{orderNumber}' => $order->reference,
            '{countryCode}' => $countryCode,
        ];

        $content = str_ireplace(
            array_keys($filters),
            array_values($filters),
            $methodDescription
        );

        return empty($content) ? $order->reference : $content;
    }
}
