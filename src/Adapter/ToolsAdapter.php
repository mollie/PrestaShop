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
use Mollie\Adapter\Context as ContextAdapter;
use Mollie\Utility\VersionUtility;
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
        if (VersionUtility::isPsVersionGreaterOrEqualTo('9.0.0')) {
            $contextAdapter = new ContextAdapter();
            $isoCode = $currency && isset($currency->iso_code)
                ? $currency->iso_code
                : $contextAdapter->getCurrencyIso();

            return $contextAdapter->formatPrice($price, $isoCode);
        }

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

    public function displayDate($date, $full = false)
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('9.0.0')) {
            return Tools::displayDate($date, $full);
        }

        return Tools::displayDate($date, null, $full);
    }

    /**
     * Redirects to an admin URL in a way that works with both legacy controllers
     * (that expose setRedirectAfter) and Symfony/profiler proxy controllers
     * (which do not implement that method).
     */
    public function redirectAdminSafe(string $url): void
    {
        $controller = \Context::getContext()->controller ?? null;

        if ($controller && method_exists($controller, 'setRedirectAfter')) {
            $controller->setRedirectAfter($url);
            return;
        }

        header('Location: ' . $url);
        exit;
    }
}
