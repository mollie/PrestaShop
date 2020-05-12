<?php

namespace Mollie\Utility;

use Mollie\Config\Config;

class ImageUtility
{
    public static function setOptionImage($image, $imageConfig)
    {
        if ($imageConfig === Config::LOGOS_NORMAL) {
            return $image['svg'];
        } elseif ($imageConfig === Config::LOGOS_BIG) {
            return $image['size2x'];
        }
    }
}