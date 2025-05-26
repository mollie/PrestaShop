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

use Transliterator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TextFormatUtility
{
    public static function formatNumber($unitPrice, $apiRoundingPrecision, $docPoint = '.', $thousandSep = '')
    {
        return number_format($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);
    }

    /**
     * Replace all accented chars by their equivalent non-accented chars.
     *
     * @param string $string
     *
     * @return string
     */
    public static function replaceAccentedChars(string $string): string
    {
        if (!class_exists(Transliterator::class)
            || !method_exists(Transliterator::class, 'create')
        ) {
            return $string;
        }

        $transliterator = Transliterator::create('Any-Latin; Latin-ASCII');

        return $transliterator->transliterate($string);
    }
}
