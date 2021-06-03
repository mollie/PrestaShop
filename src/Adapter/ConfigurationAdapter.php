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

namespace Mollie\Adapter;

use Shop;

class ConfigurationAdapter
{
	public function get($id)
	{
		return \Configuration::get($id);
	}

    public static function updateValue($key, $values, $html = false, $idShopGroup = null, $idShop = null)
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
