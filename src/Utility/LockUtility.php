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

if (!defined('_PS_VERSION_')) {
    exit;
}

class LockUtility
{
    /**
     * The webhook and the return flow both create the order for a cart, so they have
     * to lock one resource: separate names let each of them create it.
     *
     * @param string $securityToken md5 hash of the cart secure key
     *
     * @return string
     */
    public static function orderCreation($securityToken)
    {
        return sprintf('webhook-%s', $securityToken);
    }
}
