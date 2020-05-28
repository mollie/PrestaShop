<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Loader;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\FileLocator;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\Loader\LoaderResolver;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
class DirectoryLoaderTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    private static $fixturesPath;
    private $container;
    private $loader;
    public static function setUpBeforeClass()
    {
        self::$fixturesPath = \realpath(__DIR__ . '/../Fixtures/');
    }
    protected function setUp()
    {
        $locator = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\FileLocator(self::$fixturesPath);
        $this->container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->loader = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\DirectoryLoader($this->container, $locator);
        $resolver = new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\Loader\LoaderResolver([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($this->container, $locator), new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\IniFileLoader($this->container, $locator), new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($this->container, $locator), $this->loader]);
        $this->loader->setResolver($resolver);
    }
    public function testDirectoryCanBeLoadedRecursively()
    {
        $this->loader->load('directory/');
        $this->assertEquals(['ini' => 'ini', 'yaml' => 'yaml', 'php' => 'php'], $this->container->getParameterBag()->all(), '->load() takes a single directory');
    }
    public function testImports()
    {
        $this->loader->resolve('directory/import/import.yml')->load('directory/import/import.yml');
        $this->assertEquals(['ini' => 'ini', 'yaml' => 'yaml'], $this->container->getParameterBag()->all(), '->load() takes a single file that imports a directory');
    }
    public function testExceptionIsRaisedWhenDirectoryDoesNotExist()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The file "foo" does not exist (in:');
        $this->loader->load('foo/');
    }
    public function testSupports()
    {
        $loader = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\DirectoryLoader(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder(), new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\FileLocator());
        $this->assertTrue($loader->supports('directory/'), '->supports("directory/") returns true');
        $this->assertTrue($loader->supports('directory/', 'directory'), '->supports("directory/", "directory") returns true');
        $this->assertFalse($loader->supports('directory'), '->supports("directory") returns false');
        $this->assertTrue($loader->supports('directory', 'directory'), '->supports("directory", "directory") returns true');
        $this->assertFalse($loader->supports('directory', 'foo'), '->supports("directory", "foo") returns false');
    }
}
