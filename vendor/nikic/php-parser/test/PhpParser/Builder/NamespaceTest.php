<?php

namespace MolliePrefix\PhpParser\Builder;

use MolliePrefix\PhpParser\Comment\Doc;
use MolliePrefix\PhpParser\Node;
use MolliePrefix\PhpParser\Node\Stmt;
class NamespaceTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    protected function createNamespaceBuilder($fqn)
    {
        return new \MolliePrefix\PhpParser\Builder\Namespace_($fqn);
    }
    public function testCreation()
    {
        $stmt1 = new \MolliePrefix\PhpParser\Node\Stmt\Class_('SomeClass');
        $stmt2 = new \MolliePrefix\PhpParser\Node\Stmt\Interface_('SomeInterface');
        $stmt3 = new \MolliePrefix\PhpParser\Node\Stmt\Function_('someFunction');
        $docComment = new \MolliePrefix\PhpParser\Comment\Doc('/** Test */');
        $expected = new \MolliePrefix\PhpParser\Node\Stmt\Namespace_(new \MolliePrefix\PhpParser\Node\Name('MolliePrefix\\Name\\Space'), array($stmt1, $stmt2, $stmt3), array('comments' => array($docComment)));
        $node = $this->createNamespaceBuilder('MolliePrefix\\Name\\Space')->addStmt($stmt1)->addStmts(array($stmt2, $stmt3))->setDocComment($docComment)->getNode();
        $this->assertEquals($expected, $node);
        $node = $this->createNamespaceBuilder(new \MolliePrefix\PhpParser\Node\Name(array('Name', 'Space')))->setDocComment($docComment)->addStmts(array($stmt1, $stmt2))->addStmt($stmt3)->getNode();
        $this->assertEquals($expected, $node);
        $node = $this->createNamespaceBuilder(null)->getNode();
        $this->assertNull($node->name);
        $this->assertEmpty($node->stmts);
    }
}
