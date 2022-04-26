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

namespace Mollie\Utility\ApplePayDirect;

use Cart;
use Mollie\DTO\ApplePay\Carrier\Carrier as AppleCarrier;

class ShippingMethodUtility
{
    /**
     * @param AppleCarrier[] $carriers
     * @param Cart $cart
     *
     * @return array|array[]
     *
     * @throws \Exception
     */
    public static function collectShippingMethodData(array $carriers, Cart $cart)
    {
        return array_map(function (AppleCarrier $carrier) use ($cart) {
            return [
                'identifier' => $carrier->getCarrierId(),
                'label' => $carrier->getName(),
                'amount' => number_format($cart->getOrderTotal(true, Cart::ONLY_SHIPPING, null, $carrier->getCarrierId()), 2, '.', ''),
                'detail' => $carrier->getDelay(),
            ];
        }, $carriers);
    }
}
