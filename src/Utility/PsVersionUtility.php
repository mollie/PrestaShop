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

class PsVersionUtility
{
    public static function isPsVersionHigherOrEqualsThan(string $psVersion, string $targetVersion)
    {
        return version_compare($psVersion, $targetVersion, '>=');
    }
}
