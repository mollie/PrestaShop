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

use Context;
use Shop;

class ConfigurationAdapter
{
    public function get($key, $idShop = null, $idLang = null, $idShopGroup = null)
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        if (!$idShopGroup) {
            $idShopGroup = Context::getContext()->shop->id_shop_group;
        }

        return \Configuration::get($key, $idLang, $idShopGroup, $idShop);
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
