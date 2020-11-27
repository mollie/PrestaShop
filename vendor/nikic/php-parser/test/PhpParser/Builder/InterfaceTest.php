<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Scalar\DNumber;
use MolliePrefix\PhpParser\Node\Stmt;
class InterfaceTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /** @var Interface_ */
    protected $builder;
    protected function setUp()
    {
        $this->builder = new \MolliePrefix\PhpParser\Builder\Interface_('Contract');
    }
    private function dump($node)
    {
        $pp = new \MolliePrefix\PhpParser\PrettyPrinter\Standard();
        return $pp->prettyPrint(array($node));
    }
    public function testEmpty()
    {
        $contract = $this->builder->getNode();
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Stmt\\Interface_', $contract);
        $this->assertSame('Contract', $contract->name);
    }
    public function testExtending()
    {
        $contract = $this->builder->extend('MolliePrefix\\Space\\Root1', 'Root2')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Interface_('Contract', array('extends' => array(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Space\\Root1'), new \MolliePrefix\PhpParser\Node\Name('Root2')))), $contract);
    }
    public function testAddMethod()
    {
        $method = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('doSomething');
        $contract = $this->builder->addStmt($method)->getNode();
        $this->assertSame(array($method), $contract->stmts);
    }
    public function testAddConst()
    {
        $const = new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('SPEED_OF_LIGHT', new \MolliePrefix\PhpParser\Node\Scalar\DNumber(299792458.0))));
        $contract = $this->builder->addStmt($const)->getNode();
        $this->assertSame(299792458.0, $contract->stmts[0]->consts[0]->value->value);
    }
    public function testOrder()
    {
        $const = new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('SPEED_OF_LIGHT', new \MolliePrefix\PhpParser\Node\Scalar\DNumber(299792458))));
        $method = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('doSomething');
        $contract = $this->builder->addStmt($method)->addStmt($const)->getNode();
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Stmt\\ClassConst', $contract->stmts[0]);
        $this->assertInstanceOf('MolliePrefix\\PhpParser\\Node\\Stmt\\ClassMethod', $contract->stmts[1]);
    }
    public function testDocComment()
    {
        $node = $this->builder->setDocComment('/** Test */')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Interface_('Contract', array(), array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc('/** Test */')))), $node);
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unexpected node of type "Stmt_PropertyProperty"
     */
    public function testInvalidStmtError()
    {
        $this->builder->addStmt(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('invalid'));
    }
    public function testFullFunctional()
    {
        $const = new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('SPEED_OF_LIGHT', new \MolliePrefix\PhpParser\Node\Scalar\DNumber(299792458))));
        $method = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('doSomething');
        $contract = $this->builder->addStmt($method)->addStmt($const)->getNode();
        eval($this->dump($contract));
        $this->assertTrue(\interface_exists('MolliePrefix\\Contract', \false));
    }
}
