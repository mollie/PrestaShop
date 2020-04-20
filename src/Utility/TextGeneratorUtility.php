<?php

namespace Mollie\Utility;

use Cart;
use Customer;

class TextGeneratorUtility
{
    /**
     * Generate a description from the Cart
     *
     * @param Cart|int $cartId Cart or Cart ID
     * @param string $orderReference Order reference
     *
     * @return string Description
     *
     * @throws PrestaShopException
     * @throws CoreException
     * @since 3.0.0
     */
    public static function generateDescriptionFromCart($methodDescription, $cartId, $orderReference = '')
    {
        if ($cartId instanceof Cart) {
            $cart = $cartId;
        } else {
            $cart = new Cart($cartId);
        }

        $buyer = null;
        if ($cart->id_customer) {
            $buyer = new Customer($cart->id_customer);
        }

        $filters = [
            '%' => $cartId,
            '{cart.id}' => $cartId,
            '{order.reference}' => $orderReference,
            '{customer.firstname}' => $buyer == null ? '' : $buyer->firstname,
            '{customer.lastname}' => $buyer == null ? '' : $buyer->lastname,
            '{customer.company}' => $buyer == null ? '' : $buyer->company,
        ];

        $content = str_ireplace(
            array_keys($filters),
            array_values($filters),
            $methodDescription
        );

        return $content;
    }

}