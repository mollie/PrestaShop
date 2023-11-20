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

class FileUtility
{
    public static function isWritable(string $folderUrl): bool
    {
        return is_writable($folderUrl);
    }

    public static function fileExists(string $fileDir): bool
    {
        return file_exists($fileDir);
    }

    public static function copyFile(string $from, string $to): bool
    {
        return copy($from, $to);
    }

    public static function createDir(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir) && is_dir($dir);
    }
}
