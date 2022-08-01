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

class SecureKeyUtility
{
    public static function generateReturnKey($customerId, $cartId, $moduleName)
    {
        return HashUtility::hash($customerId . $cartId . $moduleName);
    }

    //todo: remove it after few releases
    public static function deprecatedGenerateReturnKey($secureKey, $customerId, $cartId, $moduleName)
    {
        return HashUtility::hash($secureKey . $customerId . $cartId . $moduleName);
    }
}
