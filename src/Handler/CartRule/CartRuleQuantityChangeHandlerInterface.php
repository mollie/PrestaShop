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

namespace Mollie\Handler\CartRule;

use Cart;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface CartRuleQuantityChangeHandlerInterface
{
    /**
     * @param array $cartRules
     */
    public function handle(Cart $cart, $cartRules = []);
}
