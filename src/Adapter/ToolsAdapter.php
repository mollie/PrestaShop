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
    public function strtoupper($str)
    {
        return Tools::strtoupper($str);
    }

    public function strlen($str)
    {
        return Tools::strlen($str);
    }

    public function substr($str, $start, $length = false)
    {
        return Tools::substr($str, $start, $length);
    }

    public function displayPrice($price, $currency)
    {
        return Tools::displayPrice($price, $currency);
    }
}
