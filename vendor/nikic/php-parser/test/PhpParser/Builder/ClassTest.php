<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Name;
use MolliePrefix\PhpParser\Node\Stmt;
class ClassTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function createClassBuilder($class)
    {
        return new \MolliePrefix\PhpParser\Builder\Class_($class);
    }
    public function testExtendsImplements()
    {
        $node = $this->createClassBuilder('SomeLogger')->extend('BaseLogger')->implement('MolliePrefix\\Namespaced\\Logger', new \MolliePrefix\PhpParser\Node\Name('SomeInterface'))->implement('MolliePrefix\\Fully\\Qualified', 'MolliePrefix\\namespace\\NamespaceRelative')->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Class_('SomeLogger', array('extends' => new \MolliePrefix\PhpParser\Node\Name('BaseLogger'), 'implements' => array(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Namespaced\\Logger'), new \MolliePrefix\PhpParser\Node\Name('SomeInterface'), new \MolliePrefix\PhpParser\Node\Name\FullyQualified('MolliePrefix\\Fully\\Qualified'), new \MolliePrefix\PhpParser\Node\Name\Relative('NamespaceRelative')))), $node);
    }
    public function testAbstract()
    {
        $node = $this->createClassBuilder('Test')->makeAbstract()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Class_('Test', array('flags' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT)), $node);
    }
    public function testFinal()
    {
        $node = $this->createClassBuilder('Test')->makeFinal()->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Class_('Test', array('flags' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_FINAL)), $node);
    }
    public function testStatementOrder()
    {
        $method = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('testMethod');
        $property = new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC, array(new \MolliePrefix\PhpParser\Node\Stmt\PropertyProperty('testProperty')));
        $const = new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('TEST_CONST', new \MolliePrefix\PhpParser\Node\Scalar\String_('ABC'))));
        $use = new \MolliePrefix\PhpParser\Node\Stmt\TraitUse(array(new \MolliePrefix\PhpParser\Node\Name('SomeTrait')));
        $node = $this->createClassBuilder('Test')->addStmt($method)->addStmt($property)->addStmts(array($const, $use))->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Class_('Test', array('stmts' => array($use, $const, $property, $method))), $node);
    }
    public function testDocComment()
    {
        $docComment = <<<'DOC'
/**
 * Test
 */
DOC;
        $class = $this->createClassBuilder('Test')->setDocComment($docComment)->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Class_('Test', array(), array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc($docComment)))), $class);
        $class = $this->createClassBuilder('Test')->setDocComment(new \MolliePrefix\PhpParser\Comment\Doc($docComment))->getNode();
        $this->assertEquals(new \MolliePrefix\PhpParser\Node\Stmt\Class_('Test', array(), array('comments' => array(new \MolliePrefix\PhpParser\Comment\Doc($docComment)))), $class);
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unexpected node of type "Stmt_Echo"
     */
    public function testInvalidStmtError()
    {
        $this->createClassBuilder('Test')->addStmt(new \MolliePrefix\PhpParser\Node\Stmt\Echo_(array()));
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Doc comment must be a string or an instance of PhpParser\Comment\Doc
     */
    public function testInvalidDocComment()
    {
        $this->createClassBuilder('Test')->setDocComment(new \MolliePrefix\PhpParser\Comment('Test'));
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Name cannot be empty
     */
    public function testEmptyName()
    {
        $this->createClassBuilder('Test')->extend('');
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Name must be a string or an instance of PhpParser\Node\Name
     */
    public function testInvalidName()
    {
        $this->createClassBuilder('Test')->extend(array('Foo'));
    }
}
