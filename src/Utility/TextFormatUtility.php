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
     * Percent-encodes every byte that is not allowed raw in a URL. PrestaShop keeps
     * symbols and non-Latin letters in link_rewrite when accented URLs are enabled,
     * and Mollie rejects such raw URLs. The encoded URL still resolves to the same page.
     */
    public static function encodeUrl(string $url): string
    {
        return (string) preg_replace_callback(
            '/[^\x21-\x7E]|["<>\\\\^`{|}]/',
            static function (array $match): string {
                return rawurlencode($match[0]);
            },
            $url
        );
    }
}
