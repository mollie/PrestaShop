<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Loader;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\FileLocator;
use MolliePrefix\Symfony\Component\Config\Loader\FileLoader;
use MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver;
class FileLoaderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testImportWithFileLocatorDelegation()
    {
        $locatorMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\FileLocatorInterface')->getMock();
        $locatorMockForAdditionalLoader = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\FileLocatorInterface')->getMock();
        $locatorMockForAdditionalLoader->expects($this->any())->method('locate')->will($this->onConsecutiveCalls(
            ['path/to/file1'],
            // Default
            ['path/to/file1', 'path/to/file2'],
            // First is imported
            ['path/to/file1', 'path/to/file2'],
            // Second is imported
            ['path/to/file1'],
            // Exception
            ['path/to/file1', 'path/to/file2']
        ));
        $fileLoader = new \MolliePrefix\Symfony\Component\Config\Tests\Loader\TestFileLoader($locatorMock);
        $fileLoader->setSupports(\false);
        $fileLoader->setCurrentDir('.');
        $additionalLoader = new \MolliePrefix\Symfony\Component\Config\Tests\Loader\TestFileLoader($locatorMockForAdditionalLoader);
        $additionalLoader->setCurrentDir('.');
        $fileLoader->setResolver($loaderResolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver([$fileLoader, $additionalLoader]));
        // Default case
        $this->assertSame('path/to/file1', $fileLoader->import('my_resource'));
        // Check first file is imported if not already loading
        $this->assertSame('path/to/file1', $fileLoader->import('my_resource'));
        // Check second file is imported if first is already loading
        $fileLoader->addLoading('path/to/file1');
        $this->assertSame('path/to/file2', $fileLoader->import('my_resource'));
        // Check exception throws if first (and only available) file is already loading
        try {
            $fileLoader->import('my_resource');
            $this->fail('->import() throws a FileLoaderImportCircularReferenceException if the resource is already loading');
        } catch (\Exception $e) {
            $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Exception\\FileLoaderImportCircularReferenceException', $e, '->import() throws a FileLoaderImportCircularReferenceException if the resource is already loading');
        }
        // Check exception throws if all files are already loading
        try {
            $fileLoader->addLoading('path/to/file2');
            $fileLoader->import('my_resource');
            $this->fail('->import() throws a FileLoaderImportCircularReferenceException if the resource is already loading');
        } catch (\Exception $e) {
            $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Exception\\FileLoaderImportCircularReferenceException', $e, '->import() throws a FileLoaderImportCircularReferenceException if the resource is already loading');
        }
    }
    public function testImportWithGlobLikeResource()
    {
        $locatorMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\FileLocatorInterface')->getMock();
        $loader = new \MolliePrefix\Symfony\Component\Config\Tests\Loader\TestFileLoader($locatorMock);
        $this->assertSame('[foo]', $loader->import('[foo]'));
    }
    public function testImportWithNoGlobMatch()
    {
        $locatorMock = $this->getMockBuilder('MolliePrefix\\Symfony\\Component\\Config\\FileLocatorInterface')->getMock();
        $loader = new \MolliePrefix\Symfony\Component\Config\Tests\Loader\TestFileLoader($locatorMock);
        $this->assertNull($loader->import('./*.abc'));
    }
    public function testImportWithSimpleGlob()
    {
        $loader = new \MolliePrefix\Symfony\Component\Config\Tests\Loader\TestFileLoader(new \MolliePrefix\Symfony\Component\Config\FileLocator(__DIR__));
        $this->assertSame(__FILE__, \strtr($loader->import('FileLoaderTest.*'), '/', \DIRECTORY_SEPARATOR));
    }
}
class TestFileLoader extends \MolliePrefix\Symfony\Component\Config\Loader\FileLoader
{
    private $supports = \true;
    public function load($resource, $type = null)
    {
        return $resource;
    }
    public function supports($resource, $type = null)
    {
        return $this->supports;
    }
    public function addLoading($resource)
    {
        self::$loading[$resource] = \true;
    }
    public function removeLoading($resource)
    {
        unset(self::$loading[$resource]);
    }
    public function clearLoading()
    {
        self::$loading = [];
    }
    public function setSupports($supports)
    {
        $this->supports = $supports;
    }
}
