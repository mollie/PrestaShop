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

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShippingMethodUtility
{
    /**
     * @param AppleCarrier[] $carriers
     *
     * @return array|array<string, string>
     *
     * @throws \Exception
     */
    public static function collectShippingMethodData(array $carriers, Cart $cart)
    {
        $originalDeliveryOption = $cart->delivery_option;

        // same zone-fallback workaround as OrderTotalCollector: soft-deleted Apple Pay addresses
        // make explicit-carrier totals price shipping against the default-country zone
        $shippingMethods = array_map(function (AppleCarrier $carrier) use ($cart) {
            $cart->setDeliveryOption([
                $cart->id_address_delivery => $carrier->getCarrierId() . ',',
            ]);

            return [
                'identifier' => (string) $carrier->getCarrierId(),
                'label' => $carrier->getName(),
                'amount' => number_format($cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2, '.', ''),
                'detail' => $carrier->getDelay(),
            ];
        }, $carriers);

        $cart->delivery_option = $originalDeliveryOption;

        return $shippingMethods;
    }
}
