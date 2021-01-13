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
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Utility;

use Mollie\Config\Config;

class ImageUtility
{
	public static function setOptionImage($image, $imageConfig)
	{
		if (Config::LOGOS_NORMAL === $imageConfig) {
			return $image['svg'];
		} elseif (Config::LOGOS_BIG === $imageConfig) {
			return $image['size2x'];
		}
	}
}
