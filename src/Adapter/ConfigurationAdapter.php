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
use Mollie\Config\Config;
use Shop;

class ConfigurationAdapter
{
    public function get($key, $idShop = null, $idLang = null, $idShopGroup = null)
    {
        if (is_array($key)) {
            if ((int) $this->get(Config::MOLLIE_ENVIRONMENT)) {
                $key = $key['production'];
            } else {
                $key = $key['sandbox'];
            }
        }

        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        if (!$idShopGroup) {
            $idShopGroup = Context::getContext()->shop->id_shop_group;
        }

        return \Configuration::get($key, $idLang, $idShopGroup, $idShop);
    }

    /**
     * @param string|array{production: string, sandbox: string} $key
     * @param mixed $value
     * @param ?int $idShop
     * @param bool $html
     * @param ?int $idShopGroup
     *
     * @return void
     */
    public function updateValue($key, $value, $idShop = null, $html = false, $idShopGroup = null)
    {
        if (is_array($key)) {
            if ((int) $this->get(Config::MOLLIE_ENVIRONMENT)) {
                $key = $key['production'];
            } else {
                $key = $key['sandbox'];
            }
        }

        if ($idShop === null) {
            $shops = Shop::getShops(true);
            foreach ($shops as $shop) {
                \Configuration::updateValue($key, $value, $html, $shop['id_shop_group'], $shop['id_shop']);
            }

            return;
        }

        \Configuration::updateValue($key, $value, $html, $idShopGroup, $idShop);
    }

    /**
     * @param string|array{production: string, sandbox: string} $key
     */
    public function delete($key)
    {
        if (is_array($key)) {
            if ((int) $this->get(Config::MOLLIE_ENVIRONMENT)) {
                $key = $key['production'];
            } else {
                $key = $key['sandbox'];
            }
        }

        \Configuration::deleteByName($key);
    }
}
