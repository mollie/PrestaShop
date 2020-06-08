<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Tests\Node;

use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use _PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
class ArrayNodeTest extends \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function testSerialization()
    {
        $node = $this->createArrayNode();
        $node->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'));
        $serializedNode = \serialize($node);
        $unserializedNode = \unserialize($serializedNode);
        $this->assertEquals($node, $unserializedNode);
        $this->assertNotEquals($this->createArrayNode(), $unserializedNode);
    }
    public function getEvaluateData()
    {
        return [[['b' => 'a', 'b'], $this->getArrayNode()]];
    }
    public function getCompileData()
    {
        return [['["b" => "a", 0 => "b"]', $this->getArrayNode()]];
    }
    public function getDumpData()
    {
        (yield ['{"b": "a", 0: "b"}', $this->getArrayNode()]);
        $array = $this->createArrayNode();
        $array->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('c'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('a"b'));
        $array->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('d'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('_PhpScoper5eddef0da618a\\a\\b'));
        (yield ['{"a\\"b": "c", "a\\\\b": "d"}', $array]);
        $array = $this->createArrayNode();
        $array->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('c'));
        $array->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('d'));
        (yield ['["c", "d"]', $array]);
    }
    protected function getArrayNode()
    {
        $array = $this->createArrayNode();
        $array->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('a'), new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'));
        $array->addElement(new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'));
        return $array;
    }
    protected function createArrayNode()
    {
        return new \_PhpScoper5eddef0da618a\Symfony\Component\ExpressionLanguage\Node\ArrayNode();
    }
}
