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

if (!defined('_PS_VERSION_')) {
    exit;
}

class DeliveryOptionUtility
{
    /**
     * @param array $carriers rows from Carrier::getCarriersForOrder()
     * @param array $deliveryOptionList Cart::getDeliveryOptionList() result
     *
     * @return array the rows offered as the option "<id>,", the key the Apple Pay handlers write, order kept
     */
    public static function filterUsableCarriers(array $carriers, array $deliveryOptionList, int $idAddress): array
    {
        $options = $deliveryOptionList[$idAddress] ?? [];

        return array_values(array_filter($carriers, function (array $carrier) use ($options) {
            return isset($options[(int) $carrier['id_carrier'] . ',']);
        }));
    }
}
