<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Stmt;
class TraitTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function createTraitBuilder($class)
    {
        return new \MolliePrefix\PhpParser\Builder\Trait_($class);
    }
    public function testStmtAddition()
    {
        $method1 = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test1');
        $method2 = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test2');
        $method3 = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test3');
        $prop = new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('test')));
        $use = new \MolliePrefix\PhpParser\Node\Stmt\TraitUse([new \MolliePrefix\PhpParser\Node\Name('OtherTrait')]);
        $trait = $this->createTraitBuilder('TestTrait')->setDocComment('/** Nice trait */')->addStmt($method1)->addStmts([$method2, $method3])->addStmt($prop)->addStmt($use)->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Trait_('TestTrait', ['stmts' => [$use, $prop, $method1, $method2, $method3]], ['comments' => [new \MolliePrefix\PhpParser\Comment\Doc('/** Nice trait */')]]), $trait);
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unexpected node of type "Stmt_Echo"
     */
    public function testInvalidStmtError()
    {
        $this->createTraitBuilder('Test')->addStmt(new \MolliePrefix\PhpParser\Node\Stmt\Echo_(array()));
    }
}
