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

class Shop
{
    public function getShop(): \Shop
    {
        return \Context::getContext()->shop;
    }

    public function getContext()
    {
        return $this->getShop()->getContext();
    }
}
