<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TextFormatUtility
{
    public static function formatNumber($unitPrice, $apiRoundingPrecision, $docPoint = '.', $thousandSep = '')
    {
        return number_format((float) $unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);
    }

    /**
     * Makes a URL valid for the Mollie API: punycode for the host, percent-encoding for the rest.
     */
    public static function encodeUrl(string $url): string
    {
        if (!preg_match('~^([a-z][a-z0-9+.-]*://)([^/?#:]*)(.*)$~is', $url, $parts)) {
            return self::percentEncode($url);
        }

        return $parts[1] . self::encodeHost($parts[2]) . self::percentEncode($parts[3]);
    }

    private static function encodeHost(string $host): string
    {
        if (!function_exists('idn_to_ascii') || !preg_match('/[^\x21-\x7E]/', $host)) {
            return $host;
        }

        $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        return $ascii === false ? $host : $ascii;
    }

    private static function percentEncode(string $value): string
    {
        return (string) preg_replace_callback(
            '/[^\x21-\x7E]|["<>\\\\^`{|}\[\]]|%(?![0-9A-Fa-f]{2})/',
            static function (array $match): string {
                return rawurlencode($match[0]);
            },
            $value
        );
    }
}
