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

class MollieApiInputSanitizer
{
    /**
     * Sanitizes an input string for use in Mollie API address fields.
     * Normalizes typographic Unicode characters to ASCII equivalents,
     * strips unsupported characters, and truncates to the max length.
     *
     * @param string|null $input
     * @param string|null $defaultValue
     * @param int $maxLength
     *
     * @return string|null
     */
    public static function sanitize($input, $defaultValue = 'N/A', $maxLength = 100)
    {
        if (empty($input)) {
            return $defaultValue;
        }

        if (ctype_space($input)) {
            return $defaultValue;
        }

        $input = ltrim($input);
        $input = static::normalizeTypographicCharacters($input);

        return substr($input, 0, $maxLength);
    }

    /**
     * Normalizes typographic Unicode characters to ASCII equivalents and strips
     * any remaining characters not accepted by the Mollie API.
     *
     * @param string $input
     *
     * @return string
     */
    public static function normalizeTypographicCharacters($input)
    {
        $input = str_replace(
            ["\u{2018}", "\u{2019}", "\u{201A}", "\u{2039}", "\u{203A}"],
            "'",
            $input
        );
        $input = str_replace(
            ["\u{201C}", "\u{201D}", "\u{201E}", "\u{00AB}", "\u{00BB}"],
            '"',
            $input
        );
        $input = str_replace(
            ["\u{2010}", "\u{2011}", "\u{2013}", "\u{2014}", "\u{2212}"],
            '-',
            $input
        );
        $input = str_replace(
            ["\u{00A0}", "\u{2003}", "\u{2009}"],
            ' ',
            $input
        );
        $input = str_replace('&', 'and', $input);
        $input = str_replace("\u{2026}", '...', $input);

        $input = preg_replace('/[^\p{L}\p{N}\s\-\'\"\.,:;\/\(\)\+\#@!?]/u', '', $input);

        return $input;
    }
}
