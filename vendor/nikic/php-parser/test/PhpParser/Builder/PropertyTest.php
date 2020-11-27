<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Scalar;
use MolliePrefix\PhpParser\Node\Stmt;
class PropertyTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function createPropertyBuilder($name)
    {
        return new \MolliePrefix\PhpParser\Builder\Property($name);
    }
    public function testModifiers()
    {
        $node = $this->createPropertyBuilder('test')->makePrivate()->makeStatic()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE | \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('test'))), $node);
        $node = $this->createPropertyBuilder('test')->makeProtected()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('test'))), $node);
        $node = $this->createPropertyBuilder('test')->makePublic()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('test'))), $node);
    }
    public function testDocComment()
    {
        $node = $this->createPropertyBuilder('test')->setDocComment('/** Test */')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('test')), array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc('/** Test */')))), $node);
    }
    /**
     * @dataProvider provideTestDefaultValues
     */
    public function testDefaultValues($value, $expectedValueNode)
    {
        $node = $this->createPropertyBuilder('test')->setDefault($value)->getNode();
        $this->assertEquals($expectedValueNode, $node->props[0]->default);
    }
    public function provideTestDefaultValues()
    {
        return array(array(null, new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('null'))), array(\true, new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('true'))), array(\false, new \MolliePrefix\PhpParser\Node\Expr\ConstFetch(new \MolliePrefix\PhpParser\Node\Name('false'))), array(31415, new \MolliePrefix\PhpParser\Node\Scalar\LNumber(31415)), array(3.1415, new \MolliePrefix\PhpParser\Node\Scalar\DNumber(3.1415)), array('Hallo World', new \MolliePrefix\PhpParser\Node\Scalar\String_('Hallo World')), array(array(1, 2, 3), new \MolliePrefix\PhpParser\Node\Expr\Array_(array(new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(1)), new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(2)), new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(3))))), array(array('foo' => 'bar', 'bar' => 'foo'), new \MolliePrefix\PhpParser\Node\Expr\Array_(array(new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\String_('bar'), new \MolliePrefix\PhpParser\Node\Scalar\String_('foo')), new \MolliePrefix\PhpParser\Node\Expr\ArrayItem(new \MolliePrefix\PhpParser\Node\Scalar\String_('foo'), new \MolliePrefix\PhpParser\Node\Scalar\String_('bar'))))), array(new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Dir(), new \MolliePrefix\PhpParser\Node\Scalar\MagicConst\Dir()));
    }
}
