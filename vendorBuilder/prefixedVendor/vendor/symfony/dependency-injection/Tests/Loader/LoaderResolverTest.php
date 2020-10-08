<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Loader;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\FileLocator;
use MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use MolliePrefix\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
class LoaderResolverTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    private static $fixturesPath;
    /** @var LoaderResolver */
    private $resolver;
    protected function setUp()
    {
        self::$fixturesPath = \realpath(__DIR__ . '/../Fixtures/');
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $this->resolver = new \MolliePrefix\Symfony\Component\Config\Loader\LoaderResolver([new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\XmlFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/xml')), new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/yaml')), new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\IniFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/ini')), new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader($container, new \MolliePrefix\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/php')), new \MolliePrefix\Symfony\Component\DependencyInjection\Loader\ClosureLoader($container)]);
    }
    public function provideResourcesToLoad()
    {
        return [['ini_with_wrong_ext.xml', 'ini', \MolliePrefix\Symfony\Component\DependencyInjection\Loader\IniFileLoader::class], ['xml_with_wrong_ext.php', 'xml', \MolliePrefix\Symfony\Component\DependencyInjection\Loader\XmlFileLoader::class], ['php_with_wrong_ext.yml', 'php', \MolliePrefix\Symfony\Component\DependencyInjection\Loader\PhpFileLoader::class], ['yaml_with_wrong_ext.ini', 'yaml', \MolliePrefix\Symfony\Component\DependencyInjection\Loader\YamlFileLoader::class]];
    }
    /**
     * @dataProvider provideResourcesToLoad
     */
    public function testResolvesForcedType($resource, $type, $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->resolver->resolve($resource, $type));
    }
}
