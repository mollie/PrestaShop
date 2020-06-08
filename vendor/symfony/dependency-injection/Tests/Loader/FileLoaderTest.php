<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Psr\Container\ContainerInterface as PsrContainerInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator;
use _PhpScoper5eddef0da618a\Symfony\Component\Config\Loader\LoaderResolver;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\FileLoader;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\BadClasses\MissingParent;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\FooInterface;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\AnotherSub\DeeperBaz;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Baz;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\Bar;
use _PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface;
class FileLoaderTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    protected static $fixturesPath;
    public static function setUpBeforeClass()
    {
        self::$fixturesPath = \realpath(__DIR__ . '/../');
    }
    public function testImportWithGlobPattern()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath));
        $resolver = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Loader\LoaderResolver([new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\IniFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/ini')), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\XmlFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/xml')), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/php')), new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/yaml'))]);
        $loader->setResolver($resolver);
        $loader->import('{F}ixtures/{xml,yaml}/services2.{yml,xml}');
        $actual = $container->getParameterBag()->all();
        $expected = ['a string', 'foo' => 'bar', 'values' => [0, 'integer' => 4, 100 => null, 'true', \true, \false, 'on', 'off', 'float' => 1.3, 1000.3, 'a string', ['foo', 'bar']], 'mixedcase' => ['MixedCaseKey' => 'value'], 'constant' => \PHP_EOL, 'bar' => '%foo%', 'escape' => '@escapeme', 'foo_bar' => new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Reference('foo_bar')];
        $this->assertEquals(\array_keys($expected), \array_keys($actual), '->load() imports and merges imported files');
    }
    public function testRegisterClasses()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('sub_dir', 'Sub');
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/Fixtures'));
        $loader->registerClasses(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition(), 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\Sub\\', 'Prototype/%sub_dir%/*');
        $this->assertEquals(['service_container', \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\Bar::class], \array_keys($container->getDefinitions()));
        $this->assertEquals([\_PhpScoper5eddef0da618a\Psr\Container\ContainerInterface::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerInterface::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface::class], \array_keys($container->getAliases()));
    }
    public function testRegisterClassesWithExclude()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('other_dir', 'OtherDir');
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/Fixtures'));
        $loader->registerClasses(
            new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition(),
            'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\',
            'Prototype/*',
            // load everything, except OtherDir/AnotherSub & Foo.php
            'Prototype/{%other_dir%/AnotherSub,Foo.php}'
        );
        $this->assertTrue($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\Bar::class));
        $this->assertTrue($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Baz::class));
        $this->assertFalse($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class));
        $this->assertFalse($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\AnotherSub\DeeperBaz::class));
        $this->assertEquals([\_PhpScoper5eddef0da618a\Psr\Container\ContainerInterface::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerInterface::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\BarInterface::class], \array_keys($container->getAliases()));
        $loader->registerClasses(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition(), 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\', 'Prototype/*', 'Prototype/NotExistingDir');
    }
    public function testNestedRegisterClasses()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/Fixtures'));
        $prototype = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition();
        $prototype->setPublic(\true)->setPrivate(\true);
        $loader->registerClasses($prototype, 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\', 'Prototype/*');
        $this->assertTrue($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Sub\Bar::class));
        $this->assertTrue($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\OtherDir\Baz::class));
        $this->assertTrue($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class));
        $this->assertEquals([\_PhpScoper5eddef0da618a\Psr\Container\ContainerInterface::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerInterface::class, \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\FooInterface::class], \array_keys($container->getAliases()));
        $alias = $container->getAlias(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\FooInterface::class);
        $this->assertSame(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\Foo::class, (string) $alias);
        $this->assertFalse($alias->isPublic());
        $this->assertFalse($alias->isPrivate());
    }
    public function testMissingParentClass()
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->setParameter('bad_classes_dir', 'BadClasses');
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/Fixtures'));
        $loader->registerClasses((new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition())->setPublic(\false), 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\BadClasses\\', 'Prototype/%bad_classes_dir%/*');
        $this->assertTrue($container->has(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\BadClasses\MissingParent::class));
        $this->assertRegExp('{Class "?Symfony\\\\Component\\\\DependencyInjection\\\\Tests\\\\Fixtures\\\\Prototype\\\\BadClasses\\\\MissingClass"? not found}', $container->getDefinition(\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Fixtures\Prototype\BadClasses\MissingParent::class)->getErrors()[0]);
    }
    public function testRegisterClassesWithBadPrefix()
    {
        $this->expectException('_PhpScoper5eddef0da618a\\Symfony\\Component\\DependencyInjection\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessageRegExp('/Expected to find class "Symfony\\\\Component\\\\DependencyInjection\\\\Tests\\\\Fixtures\\\\Prototype\\\\Bar" in file ".+" while importing services from resource "Prototype\\/Sub\\/\\*", but it was not found\\! Check the namespace prefix used with the resource/');
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/Fixtures'));
        // the Sub is missing from namespace prefix
        $loader->registerClasses(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition(), 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\', 'Prototype/Sub/*');
    }
    /**
     * @dataProvider getIncompatibleExcludeTests
     */
    public function testRegisterClassesWithIncompatibleExclude($resourcePattern, $excludePattern)
    {
        $container = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Tests\Loader\TestFileLoader($container, new \_PhpScoper5eddef0da618a\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/Fixtures'));
        try {
            $loader->registerClasses(new \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Definition(), 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\', $resourcePattern, $excludePattern);
        } catch (\_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException $e) {
            $this->assertEquals(\sprintf('Invalid "exclude" pattern when importing classes for "Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\Prototype\\": make sure your "exclude" pattern (%s) is a subset of the "resource" pattern (%s).', $excludePattern, $resourcePattern), $e->getMessage());
        }
    }
    public function getIncompatibleExcludeTests()
    {
        (yield ['Prototype/*', 'yaml/*', \false]);
        (yield ['Prototype/OtherDir/*', 'Prototype/*', \false]);
    }
}
class TestFileLoader extends \_PhpScoper5eddef0da618a\Symfony\Component\DependencyInjection\Loader\FileLoader
{
    public function load($resource, $type = null)
    {
        return $resource;
    }
    public function supports($resource, $type = null)
    {
        return \false;
    }
}
