<?php

namespace Mollie\Utility;

use Tools;

class UrlPathUtility
{
    /**
     * @param string $mediaUri
     * @param string|null $cssMediaType
     *
     * @return array|bool|mixed|string
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getMediaPath($mediaUri, $cssMediaType = null)
    {
        if (is_array($mediaUri) || $mediaUri === null || empty($mediaUri)) {
            return false;
        }

        $urlData = parse_url($mediaUri);
        if (!is_array($urlData)) {
            return false;
        }

        if (!array_key_exists('host', $urlData)) {
            $mediaUri = '/' . ltrim(str_replace(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
            // remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
            $fileUri = _PS_ROOT_DIR_ . Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

            if (!@filemtime($fileUri) || @filesize($fileUri) === 0) {
                return false;
            }

            $mediaUri = str_replace('//', '/', $mediaUri);
        }

        if ($cssMediaType) {
            return [$mediaUri => $cssMediaType];
        }

        return $mediaUri;
    }

    /**
     * Test if directory is writable
     *
     * @param string $dir Directory path, absolute or relative
     * @param bool $recursive
     * @param null $fullReport
     * @param bool $absolute Is absolute path to directory
     *
     * @return bool
     *
     * @since 3.0.2
     */
    public static function testDir($dir, $recursive = false, &$fullReport = null, $absolute = false)
    {
        if ($absolute) {
            $absoluteDir = $dir;
        } else {
            $absoluteDir = rtrim(_PS_ROOT_DIR_, '\\/') . DIRECTORY_SEPARATOR . trim($dir, '\\/');
        }

        if (!file_exists($absoluteDir)) {
            $fullReport = sprintf('Directory %s does not exist.', $absoluteDir);

            return false;
        }

        if (!is_writable($absoluteDir)) {
            $fullReport = sprintf('Directory %s is not writable.', $absoluteDir);

            return false;
        }

        if ($recursive) {
            foreach (scandir($absoluteDir, SCANDIR_SORT_NONE) as $item) {
                $path = $absoluteDir . DIRECTORY_SEPARATOR . $item;

                if (in_array($item, ['.', '..', '.git'])
                    || is_link($path)) {
                    continue;
                }

                if (is_dir($path)) {
                    if (!static::testDir($path, $recursive, $fullReport, true)) {
                        return false;
                    }
                }

                if (!is_writable($path)) {
                    $fullReport = sprintf('File %s is not writable.', $path);

                    return false;
                }
            }
        }

        return true;
    }

}