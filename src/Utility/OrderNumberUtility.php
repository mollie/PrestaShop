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

namespace Mollie\Utility;

class OrderNumberUtility
{
    const ORDER_NUMBER_PREFIX = 'mol_';

    public static function generateOrderNumber($cartId)
    {
        return self::ORDER_NUMBER_PREFIX . uniqid($cartId, false) . time();
    }
}
