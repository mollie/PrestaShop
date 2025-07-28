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
use Mollie\Utility\VersionUtility;
use Mollie\Adapter\Context as ContextAdapter;
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
    public function displayPrice($price, $currency = null, $no_utf8 = false, $context = null): string
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('9.0.0'))
        {
            /** @var ContextAdapter $context */
            $context = $context ?: new ContextAdapter();
            $currency = $currency ?: $context->getCurrencyIso();

            return $context->formatPrice($price, $currency);
        }

        return Tools::displayPrice($price, $currency, $no_utf8, $context);
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
