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

use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigurationAdapter
{
    /** @var Context */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param string|array{production: string, sandbox: string} $key
     */
    public function get($key, $idShop = null, $idLang = null, $idShopGroup = null): ?string
    {
        $key = $this->parseKeyByEnvironment($key);

        if (!$idShop) {
            $idShop = $this->context->getShopId();
        }

        if (!$idShopGroup) {
            $idShopGroup = $this->context->getShopGroupId();
        }

        $result = \Configuration::get($key, $idLang, $idShopGroup, $idShop);

        return !empty($result) ? $result : null;
    }

    /**
     * The value as stored, or false when the key was never saved. Unlike get(), a stored "0" or ""
     * comes back as is, and a given shop is read within its own shop group.
     *
     * @param string|array{production: string, sandbox: string} $key
     *
     * @return string|int|false
     */
    public function getStored($key, ?int $idShop = null)
    {
        $key = $this->parseKeyByEnvironment($key);
        $idShopGroup = $idShop ? (int) \Shop::getGroupFromShop($idShop) : $this->context->getShopGroupId();

        return \Configuration::get($key, null, $idShopGroup, $idShop ?: $this->context->getShopId());
    }

    /**
     * @param string|array{production: string, sandbox: string} $key
     */
    public function updateValue($key, $value, $idShop = null, $html = false, $idShopGroup = null): void
    {
        $key = $this->parseKeyByEnvironment($key);

        if (!$idShop) {
            $idShop = $this->context->getShopId();
        }

        if (!$idShopGroup) {
            $idShopGroup = $this->context->getShopGroupId();
        }

        \Configuration::updateValue($key, $value, $html, $idShopGroup, $idShop);
    }

    /**
     * @param string|array{production: string, sandbox: string} $key
     */
    public function delete($key): void
    {
        \Configuration::deleteByName($this->parseKeyByEnvironment($key));
    }

    public function getAsInteger($id, ?int $shopId = null)
    {
        $result = $this->get($id, $shopId);

        if (in_array($result, ['null', 'false', '0', null, false, 0], true)) {
            return 0;
        }

        return (int) $result;
    }

    /**
     * @param string|array{production: string, sandbox: string} $key
     */
    private function parseKeyByEnvironment($key): string
    {
        if (is_array($key)) {
            if ((int) $this->get(Config::MOLLIE_ENVIRONMENT)) {
                $key = $key['production'];
            } else {
                $key = $key['sandbox'];
            }
        }

        return $key;
    }
}
