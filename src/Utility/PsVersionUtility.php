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

// TODO: Duplicate of VersionUtility.php

/**
 * @deprecated use VersionUtility instead
 */
class PsVersionUtility
{
    /**
     * @deprecated use VersionUtility::isPsVersionGreaterThan() instead
     */
    public static function isPsVersionGreaterOrEqualTo(string $psVersion, string $targetVersion)
    {
        return version_compare($psVersion, $targetVersion, '>=');
    }
}
