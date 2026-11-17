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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Address steps the Express shipping callback and the Express order share, so the address the
 * callback accepted is the one the order is built with.
 */
class ExpressAddressUtility
{
    /**
     * Wallets send the region as an ISO code (Apple Pay, Google Pay) or as a name.
     * State::getIdByName() is not scoped to a country, so its match is checked.
     */
    public static function findStateId(int $countryId, string $region): int
    {
        if ('' === $region) {
            return 0;
        }

        $stateId = (int) \State::getIdByIso($region, $countryId);

        if ($stateId) {
            return $stateId;
        }

        $stateId = (int) \State::getIdByName($region);

        return $stateId && (int) (new \State($stateId))->id_country === $countryId ? $stateId : 0;
    }

    /**
     * PS 1.7 and 8 key the delivery options by each cart line's address, and the lines of an
     * anonymous cart sit on address 0.
     */
    public static function moveCartLines(\Cart $cart, int $deliveryAddressId): void
    {
        foreach (array_unique(array_column($cart->getProducts(true), 'id_address_delivery')) as $lineAddressId) {
            if ((int) $lineAddressId === $deliveryAddressId) {
                continue;
            }

            $cart->updateAddressId((int) $lineAddressId, $deliveryAddressId);
        }
    }
}
