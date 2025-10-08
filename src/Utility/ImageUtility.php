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

use Mollie\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ImageUtility
{
    public static function setOptionImage($image, $imageConfig)
    {
        // Defensive check: ensure $image is an array
        if (!is_array($image)) {
            return null;
        }

        if (Config::LOGOS_NORMAL === $imageConfig) {
            return $image['svg'] ?? null;
        } elseif (Config::LOGOS_BIG === $imageConfig) {
            return $image['size2x'] ?? null;
        }

        return null;
    }
}
