<?php

declare(strict_types=1);

namespace Mollie\Subscription\Adapter;

use Shop;

class Configuration
{
    /**
     * @return false|string
     */
    public function get(string $key, ?int $idLang = null)
    {
        return \Configuration::get($key, $idLang);
    }

    public function updateValue(string $key, $values, ?int $idShop = null): void
    {
        if ($idShop === null) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                \Configuration::updateValue($key, $values, false, $shop['id_shop_group'], $shop['id_shop']);
            }

            return;
        }

        \Configuration::updateValue($key, $values, false, null, $idShop);
    }
}
