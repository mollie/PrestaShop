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

namespace Mollie\Adapter;

use Shop;

class ConfigurationAdapter
{
    public function get($key, $idShop = null)
    {
        return \Configuration::get($key, null, null, $idShop);
    }

    public function updateValue($key, $values, $idShop = null, $html = false, $idShopGroup = null)
    {
        if ($idShop === null) {
            $shops = Shop::getShops(true);
            foreach ($shops as $shop) {
                \Configuration::updateValue($key, $values, $html, $shop['id_shop_group'], $shop['id_shop']);
            }

            return;
        }

        \Configuration::updateValue($key, $values, $html, $idShopGroup, $idShop);
    }
}
