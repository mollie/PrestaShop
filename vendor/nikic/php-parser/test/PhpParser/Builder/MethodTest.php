<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Expr\Print_;
use MolliePrefix\PhpParser\Node\Scalar\String_;
use MolliePrefix\PhpParser\Node\Stmt;
class MethodTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function createMethodBuilder($name)
    {
        return new \MolliePrefix\PhpParser\Builder\Method($name);
    }
    public function testModifiers()
    {
        $node = $this->createMethodBuilder('test')->makePublic()->makeAbstract()->makeStatic()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('flags' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC | \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT | \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC, 'stmts' => null)), $node);
        $node = $this->createMethodBuilder('test')->makeProtected()->makeFinal()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('flags' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED | \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_FINAL)), $node);
        $node = $this->createMethodBuilder('test')->makePrivate()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('type' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE)), $node);
    }
    public function testReturnByRef()
    {
        $node = $this->createMethodBuilder('test')->makeReturnByRef()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('byRef' => \true)), $node);
    }
    public function testParams()
    {
        $param1 = new \MolliePrefix\PhpParser\Node\Param('test1');
        $param2 = new \MolliePrefix\PhpParser\Node\Param('test2');
        $param3 = new \MolliePrefix\PhpParser\Node\Param('test3');
        $node = $this->createMethodBuilder('test')->addParam($param1)->addParams(array($param2, $param3))->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('params' => array($param1, $param2, $param3))), $node);
    }
    public function testStmts()
    {
        $stmt1 = new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test1'));
        $stmt2 = new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test2'));
        $stmt3 = new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test3'));
        $node = $this->createMethodBuilder('test')->addStmt($stmt1)->addStmts(array($stmt2, $stmt3))->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('stmts' => array($stmt1, $stmt2, $stmt3))), $node);
    }
    public function testDocComment()
    {
        $node = $this->createMethodBuilder('test')->setDocComment('/** Test */')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array(), array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc('/** Test */')))), $node);
    }
    public function testReturnType()
    {
        $node = $this->createMethodBuilder('test')->setReturnType('bool')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test', array('returnType' => 'bool'), array()), $node);
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot add statements to an abstract method
     */
    public function testAddStmtToAbstractMethodError()
    {
        $this->createMethodBuilder('test')->makeAbstract()->addStmt(new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test')));
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot make method with statements abstract
     */
    public function testMakeMethodWithStmtsAbstractError()
    {
        $this->createMethodBuilder('test')->addStmt(new \MolliePrefix\PhpParser\Node\Expr\Print_(new \MolliePrefix\PhpParser\Node\Scalar\String_('test')))->makeAbstract();
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Expected parameter node, got "Name"
     */
    public function testInvalidParamError()
    {
        $this->createMethodBuilder('test')->addParam(new \MolliePrefix\PhpParser\Node\Name('foo'));
    }
}
