<?php

namespace MolliePrefix;

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
/**
 * @see https://github.com/sureshdotariya/folder-compare/
 */
class FolderComparator
{
    private $blacklist = ['.', '..', '.git', 'autoload.php', 'autoload_classmap.php', 'autoload_static.php', 'autoload_real.php', 'ClassLoader.php', 'composer.json'];
    /**
     * @param $folderA
     * @param $folderB
     * @param $reference
     *
     * @return array list of items that differ
     */
    public function compareFolders($folderA, $folderB, $reference)
    {
        $itemsDiffer = [];
        $handle = \opendir($folderA);
        while (($file = \readdir($handle)) !== \false) {
            if (\in_array($file, $this->blacklist)) {
                continue;
            }
            $fileA = $folderA . \DIRECTORY_SEPARATOR . $file;
            $fileB = $folderB . \DIRECTORY_SEPARATOR . $file;
            $fullPath = $reference . \DIRECTORY_SEPARATOR . $file;
            if (\is_file($fileA)) {
                if (!\file_exists($fileB)) {
                    $itemsDiffer[] = $fullPath . ' is missing';
                } else {
                    if (\is_file($fileB)) {
                        if (\md5_file($fileA) !== \md5_file($fileB)) {
                            $itemsDiffer[] = $fullPath . ' has different md5';
                        }
                    } elseif (\is_dir($fileB)) {
                        $itemsDiffer[] = $fullPath . ' is once a dir, once a file';
                    }
                }
            } else {
                $itemsDiffer = \array_merge($itemsDiffer, $this->compareFolders($fileA, $fileB, $fullPath));
            }
        }
        \closedir($handle);
        return $itemsDiffer;
    }
}
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
/**
 * @see https://github.com/sureshdotariya/folder-compare/
 */
\class_alias('MolliePrefix\\FolderComparator', 'MolliePrefix\\FolderComparator', \false);
