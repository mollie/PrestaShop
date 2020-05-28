<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node;

use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use _PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode;
class GetAttrNodeTest extends \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [['b', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode(0), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), ['foo' => ['b' => 'a', 'b']]], ['a', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), ['foo' => ['b' => 'a', 'b']]], ['bar', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), ['foo' => new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['baz', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), ['foo' => new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['a', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('index'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), ['foo' => ['b' => 'a', 'b'], 'index' => 'b']]];
    }
    public function getCompileData()
    {
        return [['$foo[0]', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode(0), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['$foo["b"]', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['$foo->foo', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), ['foo' => new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['$foo->foo(["b" => "a", 0 => "b"])', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), ['foo' => new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['$foo[$index]', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('index'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)]];
    }
    public function getDumpData()
    {
        return [['foo[0]', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode(0), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['foo["b"]', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['foo.foo', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), ['foo' => new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['foo.foo({"b": "a", 0: "b"})', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), ['foo' => new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['foo[index]', new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\NameNode('index'), $this->getArrayNode(), \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)]];
    }
    protected function getArrayNode()
    {
        $array = new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ArrayNode();
        $array->addElement(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('a'), new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'));
        $array->addElement(new \_PhpScoper5ece82d7231e4\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'));
        return $array;
    }
}
class Obj
{
    public $foo = 'bar';
    public function foo()
    {
        return 'baz';
    }
}
