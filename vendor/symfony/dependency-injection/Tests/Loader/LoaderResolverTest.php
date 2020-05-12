<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Tests\Loader;

use _PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator;
use _PhpScoper5ea00cc67502b\Symfony\Component\Config\Loader\LoaderResolver;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
class LoaderResolverTest extends \_PhpScoper5ea00cc67502b\PHPUnit\Framework\TestCase
{
    private static $fixturesPath;
    /** @var LoaderResolver */
    private $resolver;
    protected function setUp()
    {
        self::$fixturesPath = \realpath(__DIR__ . '/../Fixtures/');
        $container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->resolver = new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\Loader\LoaderResolver([new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\XmlFileLoader($container, new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/xml')), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/yaml')), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\IniFileLoader($container, new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/ini')), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container, new \_PhpScoper5ea00cc67502b\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/php')), new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\ClosureLoader($container)]);
    }
    public function provideResourcesToLoad()
    {
        return [['ini_with_wrong_ext.xml', 'ini', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\IniFileLoader::class], ['xml_with_wrong_ext.php', 'xml', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\XmlFileLoader::class], ['php_with_wrong_ext.yml', 'php', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\PhpFileLoader::class], ['yaml_with_wrong_ext.ini', 'yaml', \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\Loader\YamlFileLoader::class]];
    }
    /**
     * @dataProvider provideResourcesToLoad
     */
    public function testResolvesForcedType($resource, $type, $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->resolver->resolve($resource, $type));
    }
}
