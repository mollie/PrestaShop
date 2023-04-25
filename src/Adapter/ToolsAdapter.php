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

use Tools;

class ToolsAdapter
{
    public function strtoupper($str): string
    {
        return Tools::strtoupper($str);
    }

    public function strlen($str): string
    {
        return Tools::strlen($str);
    }

    public function substr($str, $start, $length = false): string
    {
        return Tools::substr($str, $start, $length);
    }

    public function displayPrice($price, $currency): string
    {
        return Tools::displayPrice($price, $currency);
    }

    public function getValue(string $key, string $defaultValue = null)
    {
        return Tools::getValue($key, $defaultValue);
    }
}
