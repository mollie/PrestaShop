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

use Configuration;
use Context;
use Mollie\Config\Config;
use Shop;

class ConfigurationAdapter
{
    /** @var GlobalShopContextInterface */
    private $globalShopContext;

    public function __construct(GlobalShopContextInterface $globalShopContext)
    {
        $this->globalShopContext = $globalShopContext;
    }

    public function get(string $key, int $idShop = null, int $idLang = null, int $idShopGroup = null): ?string
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        if (!$idLang) {
            $idLang = Context::getContext()->shop->id_shop_group;
        }

        if (!$idShopGroup) {
            $idShopGroup = Context::getContext()->shop->id_shop_group;
        }

        return Configuration::get($key, $idLang, $idShopGroup, $idShop) ?: null;
    }

    public function updateValue(string $key, string $values, bool $html = false, int $idShop = null, int $idShopGroup = null): void
    {
        if ($idShop === null) {
            $shops = Shop::getShops(true);

            foreach ($shops as $shop) {
                Configuration::updateValue($key, $values, $html, $shop['id_shop_group'], $shop['id_shop']);
            }

            return;
        }

        Configuration::updateValue($key, $values, $html, $idShopGroup, $idShop);
    }

    /**
     * @param array{sandbox: string, production: string} $idByEnvironment
     *
     * @param mixed $value
     * @param bool $html
     * @param int|null $idShop
     * @param int|null $idShopGroup
     */
    public function setByEnvironment(array $idByEnvironment, string $value, bool $html = false, int $idShop = null, int $idShopGroup = null): void
    {
        if (!$idShop) {
            $idShop = $this->globalShopContext->getShopId();
        }

        if ((int) $this->get(Config::MOLLIE_ENVIRONMENT)) {
            $id = $idByEnvironment['production'];
        } else {
            $id = $idByEnvironment['sandbox'];
        }

        Configuration::updateValue($id, $value, $html, $idShopGroup, $idShop);
    }

    /**
     * @param array{sandbox: string, production: string} $idByEnvironment
     *
     * @param int|null $idShop
     * @param int|null $idLang
     * @param int|null $idShopGroup
     *
     * @return string|null
     */
    public function getByEnvironment(array $idByEnvironment, int $idShop = null, int $idLang = null, int $idShopGroup = null): ?string
    {
        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        if (!$idLang) {
            $idLang = Context::getContext()->shop->id_shop_group;
        }

        if (!$idShopGroup) {
            $idShopGroup = Context::getContext()->shop->id_shop_group;
        }

        if ((int) $this->get(Config::MOLLIE_ENVIRONMENT)) {
            $id = $idByEnvironment['production'];
        } else {
            $id = $idByEnvironment['sandbox'];
        }

        return Configuration::get($id, $idLang, $idShopGroup, $idShop) ?: null;
    }
}
