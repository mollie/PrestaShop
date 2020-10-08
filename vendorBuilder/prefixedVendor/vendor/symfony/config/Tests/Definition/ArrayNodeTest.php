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
use MolliePrefix\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use MolliePrefix\Symfony\Component\Config\Definition\ScalarNode;
class ArrayNodeTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    public function testNormalizeThrowsExceptionWhenFalseIsNotAllowed()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidTypeException');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $node->normalize(\false);
    }
    public function testExceptionThrownOnUnrecognizedChild()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Config\\Definition\\Exception\\InvalidConfigurationException');
        $this->expectExceptionMessage('Unrecognized option "foo" under "root"');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $node->normalize(['foo' => 'bar']);
    }
    public function ignoreAndRemoveMatrixProvider()
    {
        $unrecognizedOptionException = new \MolliePrefix\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException('Unrecognized option "foo" under "root"');
        return [[\true, \true, [], 'no exception is thrown for an unrecognized child if the ignoreExtraKeys option is set to true'], [\true, \false, ['foo' => 'bar'], 'extra keys are not removed when ignoreExtraKeys second option is set to false'], [\false, \true, $unrecognizedOptionException], [\false, \false, $unrecognizedOptionException]];
    }
    /**
     * @dataProvider ignoreAndRemoveMatrixProvider
     */
    public function testIgnoreAndRemoveBehaviors($ignore, $remove, $expected, $message = '')
    {
        if ($expected instanceof \Exception) {
            $this->expectException(\get_class($expected));
            $this->expectExceptionMessage($expected->getMessage());
        }
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $node->setIgnoreExtraKeys($ignore, $remove);
        $result = $node->normalize(['foo' => 'bar']);
        $this->assertSame($expected, $result, $message);
    }
    /**
     * @dataProvider getPreNormalizationTests
     */
    public function testPreNormalize($denormalized, $normalized)
    {
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('foo');
        $r = new \ReflectionMethod($node, 'preNormalize');
        $r->setAccessible(\true);
        $this->assertSame($normalized, $r->invoke($node, $denormalized));
    }
    public function getPreNormalizationTests()
    {
        return [[['foo-bar' => 'foo'], ['foo_bar' => 'foo']], [['foo-bar_moo' => 'foo'], ['foo-bar_moo' => 'foo']], [['anything-with-dash-and-no-underscore' => 'first', 'no_dash' => 'second'], ['anything_with_dash_and_no_underscore' => 'first', 'no_dash' => 'second']], [['foo-bar' => null, 'foo_bar' => 'foo'], ['foo-bar' => null, 'foo_bar' => 'foo']]];
    }
    /**
     * @dataProvider getZeroNamedNodeExamplesData
     */
    public function testNodeNameCanBeZero($denormalized, $normalized)
    {
        $zeroNode = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(0);
        $zeroNode->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('name'));
        $fiveNode = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode(5);
        $fiveNode->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode(0));
        $fiveNode->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('new_key'));
        $rootNode = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $rootNode->addChild($zeroNode);
        $rootNode->addChild($fiveNode);
        $rootNode->addChild(new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('string_key'));
        $r = new \ReflectionMethod($rootNode, 'normalizeValue');
        $r->setAccessible(\true);
        $this->assertSame($normalized, $r->invoke($rootNode, $denormalized));
    }
    public function getZeroNamedNodeExamplesData()
    {
        return [[[0 => ['name' => 'something'], 5 => [0 => 'this won\'t work too', 'new_key' => 'some other value'], 'string_key' => 'just value'], [0 => ['name' => 'something'], 5 => [0 => 'this won\'t work too', 'new_key' => 'some other value'], 'string_key' => 'just value']]];
    }
    /**
     * @dataProvider getPreNormalizedNormalizedOrderedData
     */
    public function testChildrenOrderIsMaintainedOnNormalizeValue($prenormalized, $normalized)
    {
        $scalar1 = new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('1');
        $scalar2 = new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('2');
        $scalar3 = new \MolliePrefix\Symfony\Component\Config\Definition\ScalarNode('3');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('foo');
        $node->addChild($scalar1);
        $node->addChild($scalar3);
        $node->addChild($scalar2);
        $r = new \ReflectionMethod($node, 'normalizeValue');
        $r->setAccessible(\true);
        $this->assertSame($normalized, $r->invoke($node, $prenormalized));
    }
    public function getPreNormalizedNormalizedOrderedData()
    {
        return [[['2' => 'two', '1' => 'one', '3' => 'three'], ['2' => 'two', '1' => 'one', '3' => 'three']]];
    }
    public function testAddChildEmptyName()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Child nodes must be named.');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $childNode = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('');
        $node->addChild($childNode);
    }
    public function testAddChildNameAlreadyExists()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('A child node named "foo" already exists.');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $childNode = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('foo');
        $node->addChild($childNode);
        $childNodeWithSameName = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('foo');
        $node->addChild($childNodeWithSameName);
    }
    public function testGetDefaultValueWithoutDefaultValue()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The node at path "foo" has no default value.');
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('foo');
        $node->getDefaultValue();
    }
    public function testSetDeprecated()
    {
        $childNode = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('foo');
        $childNode->setDeprecated('"%node%" is deprecated');
        $this->assertTrue($childNode->isDeprecated());
        $this->assertSame('"foo" is deprecated', $childNode->getDeprecationMessage($childNode->getName(), $childNode->getPath()));
        $node = new \MolliePrefix\Symfony\Component\Config\Definition\ArrayNode('root');
        $node->addChild($childNode);
        $deprecationTriggered = \false;
        $deprecationHandler = function ($level, $message, $file, $line) use(&$prevErrorHandler, &$deprecationTriggered) {
            if (\E_USER_DEPRECATED === $level) {
                return $deprecationTriggered = \true;
            }
            return $prevErrorHandler ? $prevErrorHandler($level, $message, $file, $line) : \false;
        };
        $prevErrorHandler = \set_error_handler($deprecationHandler);
        $node->finalize([]);
        \restore_error_handler();
        $this->assertFalse($deprecationTriggered, '->finalize() should not trigger if the deprecated node is not set');
        $prevErrorHandler = \set_error_handler($deprecationHandler);
        $node->finalize(['foo' => []]);
        \restore_error_handler();
        $this->assertTrue($deprecationTriggered, '->finalize() should trigger if the deprecated node is set');
    }
}
