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
 */

namespace Mollie\Utility;

class TextFormatUtility
{
	public static function formatNumber($unitPrice, $apiRoundingPrecision, $docPoint = '.', $thousandSep = '')
	{
		return number_format($unitPrice, $apiRoundingPrecision, $docPoint, $thousandSep);
	}
}
