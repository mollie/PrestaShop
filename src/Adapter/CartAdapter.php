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

declare(strict_types=1);

namespace Mollie\Adapter;

use Cart;
use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartAdapter
{
    public function getCart(): Cart
    {
        return Context::getContext()->cart;
    }

    public function getProducts(): array
    {
        /* @phpstan-ignore-next-line */
        return Context::getContext()->cart ? Context::getContext()->cart->getProducts() : [];
    }
}
