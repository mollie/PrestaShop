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
use MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use MolliePrefix\Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use MolliePrefix\Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use MolliePrefix\Symfony\Component\Config\Definition\Processor;
class ArrayNodeDefinitionTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testAppendingSomeNode()
    {
        $parent = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $child = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition('child');
        $parent->children()->scalarNode('foo')->end()->scalarNode('bar')->end()->end()->append($child);
        $this->assertCount(3, $this->getField($parent, 'children'));
        $this->assertContains($child, $this->getField($parent, 'children'));
    }
    /**
     * @dataProvider providePrototypeNodeSpecificCalls
     */
    public function testPrototypeNodeSpecificOption($method, $args)
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidDefinitionException');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        \call_user_func_array([$node, $method], $args);
        $node->getNode();
    }
    public function providePrototypeNodeSpecificCalls()
    {
        return [['defaultValue', [[]]], ['addDefaultChildrenIfNoneSet', []], ['requiresAtLeastOneElement', []], ['useAttributeAsKey', ['foo']]];
    }
    public function testConcreteNodeSpecificOption()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidDefinitionException');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->addDefaultsIfNotSet()->prototype('array');
        $node->getNode();
    }
    public function testPrototypeNodesCantHaveADefaultValueWhenUsingDefaultChildren()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidDefinitionException');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->defaultValue([])->addDefaultChildrenIfNoneSet('foo')->prototype('array');
        $node->getNode();
    }
    public function testPrototypedArrayNodeDefaultWhenUsingDefaultChildren()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->addDefaultChildrenIfNoneSet()->prototype('array');
        $tree = $node->getNode();
        $this->assertEquals([[]], $tree->getDefaultValue());
    }
    /**
     * @dataProvider providePrototypedArrayNodeDefaults
     */
    public function testPrototypedArrayNodeDefault($args, $shouldThrowWhenUsingAttrAsKey, $shouldThrowWhenNotUsingAttrAsKey, $defaults)
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->addDefaultChildrenIfNoneSet($args)->prototype('array');
        try {
            $tree = $node->getNode();
            $this->assertFalse($shouldThrowWhenNotUsingAttrAsKey);
            $this->assertEquals($defaults, $tree->getDefaultValue());
        } catch (\MolliePrefix\Symfony\Component\Config\Definition\Exception\InvalidDefinitionException $e) {
            $this->assertTrue($shouldThrowWhenNotUsingAttrAsKey);
        }
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->useAttributeAsKey('attr')->addDefaultChildrenIfNoneSet($args)->prototype('array');
        try {
            $tree = $node->getNode();
            $this->assertFalse($shouldThrowWhenUsingAttrAsKey);
            $this->assertEquals($defaults, $tree->getDefaultValue());
        } catch (\MolliePrefix\Symfony\Component\Config\Definition\Exception\InvalidDefinitionException $e) {
            $this->assertTrue($shouldThrowWhenUsingAttrAsKey);
        }
    }
    public function providePrototypedArrayNodeDefaults()
    {
        return [[null, \true, \false, [[]]], [2, \true, \false, [[], []]], ['2', \false, \true, ['2' => []]], ['foo', \false, \true, ['foo' => []]], [['foo'], \false, \true, ['foo' => []]], [['foo', 'bar'], \false, \true, ['foo' => [], 'bar' => []]]];
    }
    public function testNestedPrototypedArrayNodes()
    {
        $nodeDefinition = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $nodeDefinition->addDefaultChildrenIfNoneSet()->prototype('array')->prototype('array');
        $node = $nodeDefinition->getNode();
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Definition\\PrototypedArrayNode', $node);
        $this->assertInstanceOf('MolliePrefix\\Symfony\\Component\\Config\\Definition\\PrototypedArrayNode', $node->getPrototype());
    }
    public function testEnabledNodeDefaults()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->canBeEnabled()->children()->scalarNode('foo')->defaultValue('bar')->end();
        $this->assertEquals(['enabled' => \false, 'foo' => 'bar'], $node->getNode()->getDefaultValue());
    }
    /**
     * @dataProvider getEnableableNodeFixtures
     */
    public function testTrueEnableEnabledNode($expected, $config, $message)
    {
        $processor = new \MolliePrefix\Symfony\Component\Config\Definition\Processor();
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->canBeEnabled()->children()->scalarNode('foo')->defaultValue('bar')->end();
        $this->assertEquals($expected, $processor->process($node->getNode(), $config), $message);
    }
    public function testCanBeDisabled()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->canBeDisabled();
        $this->assertTrue($this->getField($node, 'addDefaults'));
        $this->assertEquals(['enabled' => \false], $this->getField($node, 'falseEquivalent'));
        $this->assertEquals(['enabled' => \true], $this->getField($node, 'trueEquivalent'));
        $this->assertEquals(['enabled' => \true], $this->getField($node, 'nullEquivalent'));
        $nodeChildren = $this->getField($node, 'children');
        $this->assertArrayHasKey('enabled', $nodeChildren);
        $enabledNode = $nodeChildren['enabled'];
        $this->assertTrue($this->getField($enabledNode, 'default'));
        $this->assertTrue($this->getField($enabledNode, 'defaultValue'));
    }
    public function testIgnoreExtraKeys()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertFalse($this->getField($node, 'ignoreExtraKeys'));
        $result = $node->ignoreExtraKeys();
        $this->assertEquals($node, $result);
        $this->assertTrue($this->getField($node, 'ignoreExtraKeys'));
    }
    public function testNormalizeKeys()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertTrue($this->getField($node, 'normalizeKeys'));
        $result = $node->normalizeKeys(\false);
        $this->assertEquals($node, $result);
        $this->assertFalse($this->getField($node, 'normalizeKeys'));
    }
    public function testUnsetChild()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->children()->scalarNode('value')->beforeNormalization()->ifTrue(function ($value) {
            return empty($value);
        })->thenUnset()->end()->end()->end();
        $this->assertSame([], $node->getNode()->normalize(['value' => null]));
    }
    public function testPrototypeVariable()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('variable'), $node->variablePrototype());
    }
    public function testPrototypeScalar()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('scalar'), $node->scalarPrototype());
    }
    public function testPrototypeBoolean()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('boolean'), $node->booleanPrototype());
    }
    public function testPrototypeInteger()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('integer'), $node->integerPrototype());
    }
    public function testPrototypeFloat()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('float'), $node->floatPrototype());
    }
    public function testPrototypeArray()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('array'), $node->arrayPrototype());
    }
    public function testPrototypeEnum()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $this->assertEquals($node->prototype('enum'), $node->enumPrototype());
    }
    public function getEnableableNodeFixtures()
    {
        return [[['enabled' => \true, 'foo' => 'bar'], [\true], 'true enables an enableable node'], [['enabled' => \true, 'foo' => 'bar'], [null], 'null enables an enableable node'], [['enabled' => \true, 'foo' => 'bar'], [['enabled' => \true]], 'An enableable node can be enabled'], [['enabled' => \true, 'foo' => 'baz'], [['foo' => 'baz']], 'any configuration enables an enableable node'], [['enabled' => \false, 'foo' => 'baz'], [['foo' => 'baz', 'enabled' => \false]], 'An enableable node can be disabled'], [['enabled' => \false, 'foo' => 'bar'], [\false], 'false disables an enableable node']];
    }
    public function testRequiresAtLeastOneElement()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->requiresAtLeastOneElement()->integerPrototype();
        $node->getNode()->finalize([1]);
        $this->addToAssertionCount(1);
    }
    /**
     * @group legacy
     * @expectedDeprecation Using Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition::cannotBeEmpty() at path "root" has no effect, consider requiresAtLeastOneElement() instead. In 4.0 both methods will behave the same.
     */
    public function testCannotBeEmpty()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->cannotBeEmpty()->integerPrototype();
        $node->getNode()->finalize([]);
    }
    public function testSetDeprecated()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->children()->arrayNode('foo')->setDeprecated('The "%path%" node is deprecated.')->end()->end();
        $deprecatedNode = $node->getNode()->getChildren()['foo'];
        $this->assertTrue($deprecatedNode->isDeprecated());
        $this->assertSame('The "root.foo" node is deprecated.', $deprecatedNode->getDeprecationMessage($deprecatedNode->getName(), $deprecatedNode->getPath()));
    }
    /**
     * @group legacy
     * @expectedDeprecation ->cannotBeEmpty() is not applicable to concrete nodes at path "root". In 4.0 it will throw an exception.
     */
    public function testCannotBeEmptyOnConcreteNode()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition('root');
        $node->cannotBeEmpty();
        $node->getNode()->finalize([]);
    }
    protected function getField($object, $field)
    {
        $reflection = new \ReflectionProperty($object, $field);
        $reflection->setAccessible(\true);
        return $reflection->getValue($object);
    }
}
