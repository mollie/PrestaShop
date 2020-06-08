<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\Config\Tests\Definition\Builder;

use _PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase;
use _PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;
use _PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\VariableNodeDefinition as BaseVariableNodeDefinition;
class NodeBuilderTest extends \_PhpScoper5eddef0da618a\PHPUnit\Framework\TestCase
{
    public function testThrowsAnExceptionWhenTryingToCreateANonRegisteredNodeType()
    {
        $this->expectException('RuntimeException');
        $builder = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $builder->node('', 'foobar');
    }
    public function testThrowsAnExceptionWhenTheNodeClassIsNotFound()
    {
        $this->expectException('RuntimeException');
        $builder = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $builder->setNodeClass('noclasstype', '_PhpScoper5eddef0da618a\\foo\\bar\\noclass')->node('', 'noclasstype');
    }
    public function testAddingANewNodeType()
    {
        $class = \_PhpScoper5eddef0da618a\Symfony\Component\Config\Tests\Definition\Builder\SomeNodeDefinition::class;
        $builder = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node = $builder->setNodeClass('newtype', $class)->node('', 'newtype');
        $this->assertInstanceOf($class, $node);
    }
    public function testOverridingAnExistingNodeType()
    {
        $class = \_PhpScoper5eddef0da618a\Symfony\Component\Config\Tests\Definition\Builder\SomeNodeDefinition::class;
        $builder = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node = $builder->setNodeClass('variable', $class)->node('', 'variable');
        $this->assertInstanceOf($class, $node);
    }
    public function testNodeTypesAreNotCaseSensitive()
    {
        $builder = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node1 = $builder->node('', 'VaRiAbLe');
        $node2 = $builder->node('', 'variable');
        $this->assertInstanceOf(\get_class($node1), $node2);
        $builder->setNodeClass('CuStOm', \_PhpScoper5eddef0da618a\Symfony\Component\Config\Tests\Definition\Builder\SomeNodeDefinition::class);
        $node1 = $builder->node('', 'CUSTOM');
        $node2 = $builder->node('', 'custom');
        $this->assertInstanceOf(\get_class($node1), $node2);
    }
    public function testNumericNodeCreation()
    {
        $builder = new \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node = $builder->integerNode('foo')->min(3)->max(5);
        $this->assertInstanceOf('_PhpScoper5eddef0da618a\\Symfony\\Component\\Config\\Definition\\Builder\\IntegerNodeDefinition', $node);
        $node = $builder->floatNode('bar')->min(3.0)->max(5.0);
        $this->assertInstanceOf('_PhpScoper5eddef0da618a\\Symfony\\Component\\Config\\Definition\\Builder\\FloatNodeDefinition', $node);
    }
}
class SomeNodeDefinition extends \_PhpScoper5eddef0da618a\Symfony\Component\Config\Definition\Builder\VariableNodeDefinition
{
}
