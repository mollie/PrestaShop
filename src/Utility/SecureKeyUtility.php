<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 *
 * @see        https://github.com/mollie/PrestaShop
 *
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Tools;

class SecureKeyUtility
{
    public static function generateReturnKey($secureKey, $customerId, $cartId, $moduleName)
    {
        return Tools::hash($secureKey . $customerId . $cartId . $moduleName);
    }
}
