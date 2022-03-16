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

class TextFormatUtility
{
    public static function formatNumber($unitPrice, $apiRoundingPrecision, $docPoint = '.', $thousandSep = '')
    {
        return number_format($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);
    }
}
