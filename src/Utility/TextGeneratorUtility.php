<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Cart;
use Configuration;
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
    public static function generateDescriptionFromCart($methodDescription, $cartId, $orderReference)
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
            '{storeName}' => Configuration::get('PS_SHOP_NAME'),
            '{orderNumber}' => $orderReference,
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