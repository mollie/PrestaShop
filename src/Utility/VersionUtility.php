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

class VersionUtility
{
    public static function isPsVersionLessThan($version): int
    {
        return (int) version_compare(_PS_VERSION_, $version, '<');
    }

    public static function isPsVersionGreaterThan($version): int
    {
        return (int) version_compare(_PS_VERSION_, $version, '>');
    }

    public static function isPsVersionGreaterOrEqualTo($version): int
    {
        return (int) version_compare(_PS_VERSION_, $version, '>=');
    }

    public static function isPsVersionLessThanOrEqualTo($version): int
    {
        return (int) version_compare(_PS_VERSION_, $version, '<=');
    }

    public static function isPsVersionEqualTo($version): int
    {
        return (int) version_compare(_PS_VERSION_, $version, '=');
    }

    public static function current(): string
    {
        return _PS_VERSION_;
    }
}
