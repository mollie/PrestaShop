<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr\Print_;
use MolliePrefix\PhpParser\Node\Scalar\String_;
use MolliePrefix\PhpParser\Node\Stmt;
class FunctionTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function createFunctionBuilder($name)
    {
        return new \MolliePrefix\PhpParser\Builder\Function_($name);
    }
    public function testReturnByRef()
    {
        $node = $this->createFunctionBuilder('test')->makeReturnByRef()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Function_('test', array('byRef' => \true)), $node);
    }
    public function testParams()
    {
        $param1 = new \MolliePrefix\PhpParser\Node\Param('test1');
        $param2 = new \MolliePrefix\PhpParser\Node\Param('test2');
        $param3 = new \MolliePrefix\PhpParser\Node\Param('test3');
        $node = $this->createFunctionBuilder('test')->addParam($param1)->addParams(array($param2, $param3))->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Function_('test', array('params' => array($param1, $param2, $param3))), $node);
    }
    public function testStmts()
    {
        $stmt1 = new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test1'));
        $stmt2 = new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test2'));
        $stmt3 = new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test3'));
        $node = $this->createFunctionBuilder('test')->addStmt($stmt1)->addStmts(array($stmt2, $stmt3))->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Function_('test', array('stmts' => array($stmt1, $stmt2, $stmt3))), $node);
    }
    public function testDocComment()
    {
        $node = $this->createFunctionBuilder('test')->setDocComment('/** Test */')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Function_('test', array(), array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc('/** Test */')))), $node);
    }
    public function testReturnType()
    {
        $node = $this->createFunctionBuilder('test')->setReturnType('void')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Function_('test', array('returnType' => 'void'), array()), $node);
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage void type cannot be nullable
     */
    public function testInvalidNullableVoidType()
    {
        $this->createFunctionBuilder('test')->setReturnType('?void');
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Expected parameter node, got "Name"
     */
    public function testInvalidParamError()
    {
        $this->createFunctionBuilder('test')->addParam(new \MolliePrefix\PhpParser\Node\Name('foo'));
    }
}
