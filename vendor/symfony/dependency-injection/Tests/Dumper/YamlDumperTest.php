<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Tests\Dumper;

use _PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase;
use _PhpScoper5ece82d7231e4\Symfony\Component\Config\FileLocator;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerInterface;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use _PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference;
use _PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Parser;
use _PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Yaml;
class YamlDumperTest extends \_PhpScoper5ece82d7231e4\PHPUnit\Framework\TestCase
{
    protected static $fixturesPath;
    public static function setUpBeforeClass()
    {
        self::$fixturesPath = \realpath(__DIR__ . '/../Fixtures/');
    }
    public function testDump()
    {
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder());
        $this->assertEqualYamlStructure(\file_get_contents(self::$fixturesPath . '/yaml/services1.yml'), $dumper->dump(), '->dump() dumps an empty container as an empty YAML file');
    }
    public function testAddParameters()
    {
        $container = (include self::$fixturesPath . '/containers/container8.php');
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container);
        $this->assertEqualYamlStructure(\file_get_contents(self::$fixturesPath . '/yaml/services8.yml'), $dumper->dump(), '->dump() dumps parameters');
    }
    public function testAddService()
    {
        $container = (include self::$fixturesPath . '/containers/container9.php');
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container);
        $this->assertEqualYamlStructure(\str_replace('%path%', self::$fixturesPath . \DIRECTORY_SEPARATOR . 'includes' . \DIRECTORY_SEPARATOR, \file_get_contents(self::$fixturesPath . '/yaml/services9.yml')), $dumper->dump(), '->dump() dumps services');
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder());
        $container->register('foo', 'FooClass')->addArgument(new \stdClass())->setPublic(\true);
        try {
            $dumper->dump();
            $this->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\\RuntimeException', $e, '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
            $this->assertEquals('Unable to dump a service container if a parameter is an object or a resource.', $e->getMessage(), '->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
        }
    }
    public function testDumpAutowireData()
    {
        $container = (include self::$fixturesPath . '/containers/container24.php');
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/yaml/services24.yml', $dumper->dump());
    }
    public function testDumpLoad()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Loader\YamlFileLoader($container, new \_PhpScoper5ece82d7231e4\Symfony\Component\Config\FileLocator(self::$fixturesPath . '/yaml'));
        $loader->load('services_dump_load.yml');
        $this->assertEquals([new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Reference('bar', \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE)], $container->getDefinition('foo')->getArguments());
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/yaml/services_dump_load.yml', $dumper->dump());
    }
    public function testInlineServices()
    {
        $container = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'Class1')->setPublic(\true)->addArgument((new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('Class2'))->addArgument(new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Definition('Class2')));
        $dumper = new \_PhpScoper5ece82d7231e4\Symfony\Component\DependencyInjection\Dumper\YamlDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/yaml/services_inline.yml', $dumper->dump());
    }
    private function assertEqualYamlStructure($expected, $yaml, $message = '')
    {
        $parser = new \_PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Parser();
        $this->assertEquals($parser->parse($expected, \_PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Yaml::PARSE_CUSTOM_TAGS), $parser->parse($yaml, \_PhpScoper5ece82d7231e4\Symfony\Component\Yaml\Yaml::PARSE_CUSTOM_TAGS), $message);
    }
}
