<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\DependencyInjection\Tests;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator;
/**
 * @group legacy
 */
class DefinitionDecoratorTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $this->assertEquals('foo', $def->getParent());
        $this->assertEquals([], $def->getChanges());
    }
    /**
     * @dataProvider getPropertyTests
     */
    public function testSetProperty($property, $changeKey)
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $getter = 'get' . \ucfirst($property);
        $setter = 'set' . \ucfirst($property);
        $this->assertNull($def->{$getter}());
        $this->assertSame($def, $def->{$setter}('foo'));
        $this->assertEquals('foo', $def->{$getter}());
        $this->assertEquals([$changeKey => \true], $def->getChanges());
    }
    public function getPropertyTests()
    {
        return [['class', 'class'], ['factory', 'factory'], ['configurator', 'configurator'], ['file', 'file']];
    }
    public function testSetPublic()
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $this->assertTrue($def->isPublic());
        $this->assertSame($def, $def->setPublic(\false));
        $this->assertFalse($def->isPublic());
        $this->assertEquals(['public' => \true], $def->getChanges());
    }
    public function testSetLazy()
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $this->assertFalse($def->isLazy());
        $this->assertSame($def, $def->setLazy(\false));
        $this->assertFalse($def->isLazy());
        $this->assertEquals(['lazy' => \true], $def->getChanges());
    }
    public function testSetAutowired()
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $this->assertFalse($def->isAutowired());
        $this->assertSame($def, $def->setAutowired(\true));
        $this->assertTrue($def->isAutowired());
        $this->assertSame(['autowired' => \true], $def->getChanges());
    }
    public function testSetArgument()
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $this->assertEquals([], $def->getArguments());
        $this->assertSame($def, $def->replaceArgument(0, 'foo'));
        $this->assertEquals(['index_0' => 'foo'], $def->getArguments());
    }
    public function testReplaceArgumentShouldRequireIntegerIndex()
    {
        $this->expectException('InvalidArgumentException');
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $def->replaceArgument('0', 'foo');
    }
    public function testReplaceArgument()
    {
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $def->setArguments([0 => 'foo', 1 => 'bar']);
        $this->assertEquals('foo', $def->getArgument(0));
        $this->assertEquals('bar', $def->getArgument(1));
        $this->assertSame($def, $def->replaceArgument(1, 'baz'));
        $this->assertEquals('foo', $def->getArgument(0));
        $this->assertEquals('baz', $def->getArgument(1));
        $this->assertEquals([0 => 'foo', 1 => 'bar', 'index_1' => 'baz'], $def->getArguments());
    }
    public function testGetArgumentShouldCheckBounds()
    {
        $this->expectException('OutOfBoundsException');
        $def = new \MolliePrefix\Symfony\Component\DependencyInjection\DefinitionDecorator('foo');
        $def->setArguments([0 => 'foo']);
        $def->replaceArgument(0, 'foo');
        $def->getArgument(1);
    }
}
