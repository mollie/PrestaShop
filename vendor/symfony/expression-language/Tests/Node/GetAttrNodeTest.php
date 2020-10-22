<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node;

use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArrayNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode;
use MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode;
class GetAttrNodeTest extends \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\AbstractNodeTest
{
    public function getEvaluateData()
    {
        return [['b', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(0), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), ['foo' => ['b' => 'a', 'b']]], ['a', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), ['foo' => ['b' => 'a', 'b']]], ['bar', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), ['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['baz', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), ['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['a', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('index'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL), ['foo' => ['b' => 'a', 'b'], 'index' => 'b']]];
    }
    public function getCompileData()
    {
        return [['$foo[0]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(0), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['$foo["b"]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['$foo->foo', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), ['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['$foo->foo(["b" => "a", 0 => "b"])', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('foo'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), ['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['$foo[$index]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('index'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)]];
    }
    public function getDumpData()
    {
        return [['foo[0]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode(0), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['foo["b"]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)], ['foo.foo', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::PROPERTY_CALL), ['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['foo.foo({"b": "a", 0: "b"})', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::METHOD_CALL), ['foo' => new \MolliePrefix\Symfony\Component\ExpressionLanguage\Tests\Node\Obj()]], ['foo[index]', new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('foo'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\NameNode('index'), $this->getArrayNode(), \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\GetAttrNode::ARRAY_CALL)]];
    }
    protected function getArrayNode()
    {
        $array = new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ArrayNode();
        $array->addElement(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('a'), new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'));
        $array->addElement(new \MolliePrefix\Symfony\Component\ExpressionLanguage\Node\ConstantNode('b'));
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
