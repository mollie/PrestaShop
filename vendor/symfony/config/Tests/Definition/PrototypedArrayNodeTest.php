<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Config\Tests\Definition;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Config\Definition\ArrayNode;
use MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode;
use MolliePrefix\Symfony\Component\Config\Definition\ScalarNode;
use MolliePrefix\Symfony\Component\Config\Definition\VariableNode;
class PrototypedArrayNodeTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testGetDefaultValueReturnsAnEmptyArrayForPrototypes()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('root');
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(null, $node);
        $node->setPrototype($prototype);
        $this->assertEmpty($node->getDefaultValue());
    }
    public function testGetDefaultValueReturnsDefaultValueForPrototypes()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('root');
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(null, $node);
        $node->setPrototype($prototype);
        $node->setDefaultValue(['test']);
        $this->assertEquals(['test'], $node->getDefaultValue());
    }
    // a remapped key (e.g. "mapping" -> "mappings") should be unset after being used
    public function testRemappedKeysAreUnset()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $mappingsNode = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('mappings');
        $node->addChild($mappingsNode);
        // each item under mappings is just a scalar
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode(null, $mappingsNode);
        $mappingsNode->setPrototype($prototype);
        $remappings = [];
        $remappings[] = ['mapping', 'mappings'];
        $node->setXmlRemappings($remappings);
        $normalized = $node->normalize(['mapping' => ['foo', 'bar']]);
        $this->assertEquals(['mappings' => ['foo', 'bar']], $normalized);
    }
    /**
     * Tests that when a key attribute is mapped, that key is removed from the array.
     *
     *     <things>
     *         <option id="option1" value="foo">
     *         <option id="option2" value="bar">
     *     </things>
     *
     * The above should finally be mapped to an array that looks like this
     * (because "id" is the key attribute).
     *
     *     [
     *         'things' => [
     *             'option1' => 'foo',
     *             'option2' => 'bar',
     *         ]
     *     ]
     */
    public function testMappedAttributeKeyIsRemoved()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('root');
        $node->setKeyAttribute('id', \true);
        // each item under the root is an array, with one scalar item
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(null, $node);
        $prototype->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('foo'));
        $node->setPrototype($prototype);
        $children = [];
        $children[] = ['id' => 'item_name', 'foo' => 'bar'];
        $normalized = $node->normalize($children);
        $expected = [];
        $expected['item_name'] = ['foo' => 'bar'];
        $this->assertEquals($expected, $normalized);
    }
    /**
     * Tests the opposite of the testMappedAttributeKeyIsRemoved because
     * the removal can be toggled with an option.
     */
    public function testMappedAttributeKeyNotRemoved()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('root');
        $node->setKeyAttribute('id', \false);
        // each item under the root is an array, with two scalar items
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(null, $node);
        $prototype->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('foo'));
        $prototype->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('id'));
        // the key attribute will remain
        $node->setPrototype($prototype);
        $children = [];
        $children[] = ['id' => 'item_name', 'foo' => 'bar'];
        $normalized = $node->normalize($children);
        $expected = [];
        $expected['item_name'] = ['id' => 'item_name', 'foo' => 'bar'];
        $this->assertEquals($expected, $normalized);
    }
    public function testAddDefaultChildren()
    {
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet();
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals([['foo' => 'bar']], $node->getDefaultValue());
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet();
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(['defaults' => ['foo' => 'bar']], $node->getDefaultValue());
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet('defaultkey');
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(['defaultkey' => ['foo' => 'bar']], $node->getDefaultValue());
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet(['defaultkey']);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(['defaultkey' => ['foo' => 'bar']], $node->getDefaultValue());
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setKeyAttribute('foobar');
        $node->setAddChildrenIfNoneSet(['dk1', 'dk2']);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals(['dk1' => ['foo' => 'bar'], 'dk2' => ['foo' => 'bar']], $node->getDefaultValue());
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet([5, 6]);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals([0 => ['foo' => 'bar'], 1 => ['foo' => 'bar']], $node->getDefaultValue());
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet(2);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals([['foo' => 'bar'], ['foo' => 'bar']], $node->getDefaultValue());
    }
    public function testDefaultChildrenWinsOverDefaultValue()
    {
        $node = $this->getPrototypeNodeWithDefaultChildren();
        $node->setAddChildrenIfNoneSet();
        $node->setDefaultValue(['bar' => 'foo']);
        $this->assertTrue($node->hasDefaultValue());
        $this->assertEquals([['foo' => 'bar']], $node->getDefaultValue());
    }
    protected function getPrototypeNodeWithDefaultChildren()
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('root');
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(null, $node);
        $child = new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('foo');
        $child->setDefaultValue('bar');
        $prototype->addChild($child);
        $prototype->setAddIfNotSet(\true);
        $node->setPrototype($prototype);
        return $node;
    }
    /**
     * Tests that when a key attribute is mapped, that key is removed from the array.
     * And if only 'value' element is left in the array, it will replace its wrapper array.
     *
     *     <things>
     *         <option id="option1" value="value1">
     *     </things>
     *
     * The above should finally be mapped to an array that looks like this
     * (because "id" is the key attribute).
     *
     *     [
     *         'things' => [
     *             'option1' => 'value1'
     *         ]
     *     ]
     *
     * It's also possible to mix 'value-only' and 'non-value-only' elements in the array.
     *
     * <things>
     *     <option id="option1" value="value1">
     *     <option id="option2" value="value2" foo="foo2">
     * </things>
     *
     * The above should finally be mapped to an array as follows
     *
     * [
     *     'things' => [
     *         'option1' => 'value1',
     *         'option2' => [
     *             'value' => 'value2',
     *             'foo' => 'foo2'
     *         ]
     *     ]
     * ]
     *
     * The 'value' element can also be ArrayNode:
     *
     * <things>
     *     <option id="option1">
     *         <value>
     *            <foo>foo1</foo>
     *            <bar>bar1</bar>
     *         </value>
     *     </option>
     * </things>
     *
     * The above should be finally be mapped to an array as follows
     *
     * [
     *     'things' => [
     *         'option1' => [
     *             'foo' => 'foo1',
     *             'bar' => 'bar1'
     *         ]
     *     ]
     * ]
     *
     * If using VariableNode for value node, it's also possible to mix different types of value nodes:
     *
     * <things>
     *     <option id="option1">
     *         <value>
     *            <foo>foo1</foo>
     *            <bar>bar1</bar>
     *         </value>
     *     </option>
     *     <option id="option2" value="value2">
     * </things>
     *
     * The above should be finally mapped to an array as follows
     *
     * [
     *     'things' => [
     *         'option1' => [
     *             'foo' => 'foo1',
     *             'bar' => 'bar1'
     *         ],
     *         'option2' => 'value2'
     *     ]
     * ]
     *
     * @dataProvider getDataForKeyRemovedLeftValueOnly
     */
    public function testMappedAttributeKeyIsRemovedLeftValueOnly($value, $children, $expected)
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\PrototypedArrayNode('root');
        $node->setKeyAttribute('id', \true);
        // each item under the root is an array, with one scalar item
        $prototype = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(null, $node);
        $prototype->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('id'));
        $prototype->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('foo'));
        $prototype->addChild($value);
        $node->setPrototype($prototype);
        $normalized = $node->normalize($children);
        $this->assertEquals($expected, $normalized);
    }
    public function getDataForKeyRemovedLeftValueOnly()
    {
        $scalarValue = new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('value');
        $arrayValue = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('value');
        $arrayValue->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('foo'));
        $arrayValue->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('bar'));
        $variableValue = new \MolliePrefix\Symfony\Component\Config\Definition\VariableNode('value');
        return [[$scalarValue, [['id' => 'option1', 'value' => 'value1']], ['option1' => 'value1']], [$scalarValue, [['id' => 'option1', 'value' => 'value1'], ['id' => 'option2', 'value' => 'value2', 'foo' => 'foo2']], ['option1' => 'value1', 'option2' => ['value' => 'value2', 'foo' => 'foo2']]], [$arrayValue, [['id' => 'option1', 'value' => ['foo' => 'foo1', 'bar' => 'bar1']]], ['option1' => ['foo' => 'foo1', 'bar' => 'bar1']]], [$variableValue, [['id' => 'option1', 'value' => ['foo' => 'foo1', 'bar' => 'bar1']], ['id' => 'option2', 'value' => 'value2']], ['option1' => ['foo' => 'foo1', 'bar' => 'bar1'], 'option2' => 'value2']]];
    }
}
