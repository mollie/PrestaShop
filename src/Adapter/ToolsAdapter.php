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

use Currency;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ToolsAdapter
{
    public function strlen($str): string
    {
        return Tools::strlen($str);
    }

    public function substr($str, $start, $length = false): string
    {
        return Tools::substr($str, $start, $length);
    }

    /**
     * @param float $price
     * @param int|Currency|array|null $currency Current currency (object, id_currency, NULL => context currency)
     *
     * @return string
     *
     * @throws LocalizationException
     */
    public function displayPrice($price, $currency = null): string
    {
        // TODO replace all displayPrice calls with Locale::formatPrice()
        return Tools::displayPrice($price, $currency);
    }

    public function getValue(string $key, string $defaultValue = null)
    {
        $result = Tools::getValue($key, $defaultValue);

        return !empty($result) ? $result : null;
    }

    public function isSubmit(string $string): bool
    {
        return (bool) Tools::isSubmit($string);
    }

    public function getValueAsInt($value, $defaultValue = 0)
    {
        return (int) Tools::getValue($value, $defaultValue);
    }
}
