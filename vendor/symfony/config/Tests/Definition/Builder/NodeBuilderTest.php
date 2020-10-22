<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Definition\Builder;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder as BaseNodeBuilder;
use MolliePrefix\Symfony\Component\Config\Definition\Builder\VariableNodeDefinition as BaseVariableNodeDefinition;
class NodeBuilderTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testThrowsAnExceptionWhenTryingToCreateANonRegisteredNodeType()
    {
        $this->expectException('RuntimeException');
        $builder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $builder->node('', 'foobar');
    }
    public function testThrowsAnExceptionWhenTheNodeClassIsNotFound()
    {
        $this->expectException('RuntimeException');
        $builder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $builder->setNodeClass('noclasstype', 'MolliePrefix\\foo\\bar\\noclass')->node('', 'noclasstype');
    }
    public function testAddingANewNodeType()
    {
        $class = \MolliePrefix\Symfony\Component\Config\Tests\Definition\Builder\SomeNodeDefinition::class;
        $builder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node = $builder->setNodeClass('newtype', $class)->node('', 'newtype');
        $this->assertInstanceOf($class, $node);
    }
    public function testOverridingAnExistingNodeType()
    {
        $class = \MolliePrefix\Symfony\Component\Config\Tests\Definition\Builder\SomeNodeDefinition::class;
        $builder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node = $builder->setNodeClass('variable', $class)->node('', 'variable');
        $this->assertInstanceOf($class, $node);
    }
    public function testNodeTypesAreNotCaseSensitive()
    {
        $builder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node1 = $builder->node('', 'VaRiAbLe');
        $node2 = $builder->node('', 'variable');
        $this->assertInstanceOf(\get_class($node1), $node2);
        $builder->setNodeClass('CuStOm', \MolliePrefix\Symfony\Component\Config\Tests\Definition\Builder\SomeNodeDefinition::class);
        $node1 = $builder->node('', 'CUSTOM');
        $node2 = $builder->node('', 'custom');
        $this->assertInstanceOf(\get_class($node1), $node2);
    }
    public function testNumericNodeCreation()
    {
        $builder = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\NodeBuilder();
        $node = $builder->integerNode('foo')->min(3)->max(5);
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Builder\\IntegerNodeDefinition', $node);
        $node = $builder->floatNode('bar')->min(3.0)->max(5.0);
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Builder\\FloatNodeDefinition', $node);
    }
}
class SomeNodeDefinition extends \MolliePrefix\Symfony\Component\Config\Definition\Builder\VariableNodeDefinition
{
}
