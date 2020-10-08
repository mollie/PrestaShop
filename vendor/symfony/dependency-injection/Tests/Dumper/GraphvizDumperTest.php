<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests\Dumper;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder;
use MolliePrefix\Symfony\Component\DependencyInjection\Definition;
use MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper;
use MolliePrefix\Symfony\Component\DependencyInjection\Reference;
class GraphvizDumperTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    protected static $fixturesPath;
    public static function setUpBeforeClass()
    {
        self::$fixturesPath = __DIR__ . '/../Fixtures/';
    }
    public function testDump()
    {
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder());
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services1.dot', $dumper->dump(), '->dump() dumps an empty container as an empty dot file');
        $container = (include self::$fixturesPath . '/containers/container9.php');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services9.dot', $dumper->dump(), '->dump() dumps services');
        $container = (include self::$fixturesPath . '/containers/container10.php');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services10.dot', $dumper->dump(), '->dump() dumps services');
        $container = (include self::$fixturesPath . '/containers/container10.php');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertEquals($dumper->dump(['graph' => ['ratio' => 'normal'], 'node' => ['fontsize' => 13, 'fontname' => 'Verdana', 'shape' => 'square'], 'edge' => ['fontsize' => 12, 'fontname' => 'Verdana', 'color' => 'white', 'arrowhead' => 'closed', 'arrowsize' => 1], 'node.instance' => ['fillcolor' => 'green', 'style' => 'empty'], 'node.definition' => ['fillcolor' => 'grey'], 'node.missing' => ['fillcolor' => 'red', 'style' => 'empty']]), \file_get_contents(self::$fixturesPath . '/graphviz/services10-1.dot'), '->dump() dumps services');
    }
    public function testDumpWithFrozenContainer()
    {
        $container = (include self::$fixturesPath . '/containers/container13.php');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services13.dot', $dumper->dump(), '->dump() dumps services');
    }
    public function testDumpWithFrozenCustomClassContainer()
    {
        $container = (include self::$fixturesPath . '/containers/container14.php');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services14.dot', $dumper->dump(), '->dump() dumps services');
    }
    public function testDumpWithUnresolvedParameter()
    {
        $container = (include self::$fixturesPath . '/containers/container17.php');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services17.dot', $dumper->dump(), '->dump() dumps services');
    }
    public function testDumpWithInlineDefinition()
    {
        $container = new \MolliePrefix\Symfony\Component\DependencyInjection\ContainerBuilder();
        $container->register('foo', 'stdClass')->addArgument((new \MolliePrefix\Symfony\Component\DependencyInjection\Definition('stdClass'))->addArgument(new \MolliePrefix\Symfony\Component\DependencyInjection\Reference('bar')));
        $container->register('bar', 'stdClass');
        $dumper = new \MolliePrefix\Symfony\Component\DependencyInjection\Dumper\GraphvizDumper($container);
        $this->assertStringEqualsFile(self::$fixturesPath . '/graphviz/services_inline.dot', $dumper->dump(), '->dump() dumps nested references');
    }
}
