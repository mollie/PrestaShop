<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
namespace MolliePrefix\PrestaShop\HeaderStamp;

/**
 * Class responsible of loading license file in memory and returning its content
 */
class LicenseHeader
{
    /**
     * Header content
     *
     * @param string $content
     */
    private $content;
    /**
     * Path to the file
     *
     * @param string $filePath
     */
    private $filePath;
    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }
    /**
     * @return string Getter for Header content
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->loadFile();
        }
        return $this->content;
    }
    /**
     * Checks the file and loads its content in memory
     */
    private function loadFile()
    {
        if (!\file_exists($this->filePath)) {
            // If the file is not found, we might have a relative path
            // We check this before throwing any exception
            $fromRelativeFilePath = \getcwd() . '/' . $this->filePath;
            $fromSrcFolderFilePath = __DIR__ . '/../' . $this->filePath;
            if (\file_exists($fromRelativeFilePath)) {
                $this->filePath = $fromRelativeFilePath;
            } elseif (\file_exists($fromSrcFolderFilePath)) {
                $this->filePath = $fromSrcFolderFilePath;
            } else {
                throw new \Exception('File ' . $this->filePath . ' does not exist.');
            }
        }
        if (!\is_readable($this->filePath)) {
            throw new \Exception('File ' . $this->filePath . ' cannot be read.');
        }
        $this->content = \file_get_contents($this->filePath);
    }
}
