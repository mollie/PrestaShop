<?php

namespace MolliePrefix\PhpParser;

use MolliePrefix\PhpParser\Node\Expr;
use MolliePrefix\PhpParser\Node\Scalar\String_;
class NodeTraverserTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testNonModifying()
    {
        $str1Node = new \MolliePrefix\PhpParser\Node\Scalar\String_('Foo');
        $str2Node = new \MolliePrefix\PhpParser\Node\Scalar\String_('Bar');
        $echoNode = new \MolliePrefix\PhpParser\Node\Stmt\Echo_(array($str1Node, $str2Node));
        $stmts = array($echoNode);
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(0))->method('beforeTraverse')->with($stmts);
        $visitor->expects($this->at(1))->method('enterNode')->with($echoNode);
        $visitor->expects($this->at(2))->method('enterNode')->with($str1Node);
        $visitor->expects($this->at(3))->method('leaveNode')->with($str1Node);
        $visitor->expects($this->at(4))->method('enterNode')->with($str2Node);
        $visitor->expects($this->at(5))->method('leaveNode')->with($str2Node);
        $visitor->expects($this->at(6))->method('leaveNode')->with($echoNode);
        $visitor->expects($this->at(7))->method('afterTraverse')->with($stmts);
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
    }
    public function testModifying()
    {
        $str1Node = new \MolliePrefix\PhpParser\Node\Scalar\String_('Foo');
        $str2Node = new \MolliePrefix\PhpParser\Node\Scalar\String_('Bar');
        $printNode = new \MolliePrefix\PhpParser\Node\Expr\Print_($str1Node);
        // first visitor changes the node, second verifies the change
        $visitor1 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor2 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        // replace empty statements with string1 node
        $visitor1->expects($this->at(0))->method('beforeTraverse')->with(array())->will($this->returnValue(array($str1Node)));
        $visitor2->expects($this->at(0))->method('beforeTraverse')->with(array($str1Node));
        // replace string1 node with print node
        $visitor1->expects($this->at(1))->method('enterNode')->with($str1Node)->will($this->returnValue($printNode));
        $visitor2->expects($this->at(1))->method('enterNode')->with($printNode);
        // replace string1 node with string2 node
        $visitor1->expects($this->at(2))->method('enterNode')->with($str1Node)->will($this->returnValue($str2Node));
        $visitor2->expects($this->at(2))->method('enterNode')->with($str2Node);
        // replace string2 node with string1 node again
        $visitor1->expects($this->at(3))->method('leaveNode')->with($str2Node)->will($this->returnValue($str1Node));
        $visitor2->expects($this->at(3))->method('leaveNode')->with($str1Node);
        // replace print node with string1 node again
        $visitor1->expects($this->at(4))->method('leaveNode')->with($printNode)->will($this->returnValue($str1Node));
        $visitor2->expects($this->at(4))->method('leaveNode')->with($str1Node);
        // replace string1 node with empty statements again
        $visitor1->expects($this->at(5))->method('afterTraverse')->with(array($str1Node))->will($this->returnValue(array()));
        $visitor2->expects($this->at(5))->method('afterTraverse')->with(array());
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor1);
        $traverser->addVisitor($visitor2);
        // as all operations are reversed we end where we start
        $this->assertEquals(array(), $traverser->traverse(array()));
    }
    public function testRemove()
    {
        $str1Node = new \MolliePrefix\PhpParser\Node\Scalar\String_('Foo');
        $str2Node = new \MolliePrefix\PhpParser\Node\Scalar\String_('Bar');
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        // remove the string1 node, leave the string2 node
        $visitor->expects($this->at(2))->method('leaveNode')->with($str1Node)->will($this->returnValue(\false));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals(array($str2Node), $traverser->traverse(array($str1Node, $str2Node)));
    }
    public function testMerge()
    {
        $strStart = new \MolliePrefix\PhpParser\Node\Scalar\String_('Start');
        $strMiddle = new \MolliePrefix\PhpParser\Node\Scalar\String_('End');
        $strEnd = new \MolliePrefix\PhpParser\Node\Scalar\String_('Middle');
        $strR1 = new \MolliePrefix\PhpParser\Node\Scalar\String_('Replacement 1');
        $strR2 = new \MolliePrefix\PhpParser\Node\Scalar\String_('Replacement 2');
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        // replace strMiddle with strR1 and strR2 by merge
        $visitor->expects($this->at(4))->method('leaveNode')->with($strMiddle)->will($this->returnValue(array($strR1, $strR2)));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals(array($strStart, $strR1, $strR2, $strEnd), $traverser->traverse(array($strStart, $strMiddle, $strEnd)));
    }
    public function testDeepArray()
    {
        $strNode = new \MolliePrefix\PhpParser\Node\Scalar\String_('Foo');
        $stmts = array(array(array($strNode)));
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(1))->method('enterNode')->with($strNode);
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
    }
    public function testDontTraverseChildren()
    {
        $strNode = new \MolliePrefix\PhpParser\Node\Scalar\String_('str');
        $printNode = new \MolliePrefix\PhpParser\Node\Expr\Print_($strNode);
        $varNode = new \MolliePrefix\PhpParser\Node\Expr\Variable('foo');
        $mulNode = new \MolliePrefix\PhpParser\Node\Expr\BinaryOp\Mul($varNode, $varNode);
        $negNode = new \MolliePrefix\PhpParser\Node\Expr\UnaryMinus($mulNode);
        $stmts = array($printNode, $negNode);
        $visitor1 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor2 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor1->expects($this->at(1))->method('enterNode')->with($printNode)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN));
        $visitor2->expects($this->at(1))->method('enterNode')->with($printNode);
        $visitor1->expects($this->at(2))->method('leaveNode')->with($printNode);
        $visitor2->expects($this->at(2))->method('leaveNode')->with($printNode);
        $visitor1->expects($this->at(3))->method('enterNode')->with($negNode);
        $visitor2->expects($this->at(3))->method('enterNode')->with($negNode);
        $visitor1->expects($this->at(4))->method('enterNode')->with($mulNode);
        $visitor2->expects($this->at(4))->method('enterNode')->with($mulNode)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN));
        $visitor1->expects($this->at(5))->method('leaveNode')->with($mulNode);
        $visitor2->expects($this->at(5))->method('leaveNode')->with($mulNode);
        $visitor1->expects($this->at(6))->method('leaveNode')->with($negNode);
        $visitor2->expects($this->at(6))->method('leaveNode')->with($negNode);
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor1);
        $traverser->addVisitor($visitor2);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
    }
    public function testStopTraversal()
    {
        $varNode1 = new \MolliePrefix\PhpParser\Node\Expr\Variable('a');
        $varNode2 = new \MolliePrefix\PhpParser\Node\Expr\Variable('b');
        $varNode3 = new \MolliePrefix\PhpParser\Node\Expr\Variable('c');
        $mulNode = new \MolliePrefix\PhpParser\Node\Expr\BinaryOp\Mul($varNode1, $varNode2);
        $printNode = new \MolliePrefix\PhpParser\Node\Expr\Print_($varNode3);
        $stmts = [$mulNode, $printNode];
        // From enterNode() with array parent
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(1))->method('enterNode')->with($mulNode)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::STOP_TRAVERSAL));
        $visitor->expects($this->at(2))->method('afterTraverse');
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
        // From enterNode with Node parent
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(2))->method('enterNode')->with($varNode1)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::STOP_TRAVERSAL));
        $visitor->expects($this->at(3))->method('afterTraverse');
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
        // From leaveNode with Node parent
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(3))->method('leaveNode')->with($varNode1)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::STOP_TRAVERSAL));
        $visitor->expects($this->at(4))->method('afterTraverse');
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
        // From leaveNode with array parent
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(6))->method('leaveNode')->with($mulNode)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::STOP_TRAVERSAL));
        $visitor->expects($this->at(7))->method('afterTraverse');
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals($stmts, $traverser->traverse($stmts));
        // Check that pending array modifications are still carried out
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->expects($this->at(6))->method('leaveNode')->with($mulNode)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::REMOVE_NODE));
        $visitor->expects($this->at(7))->method('enterNode')->with($printNode)->will($this->returnValue(\MolliePrefix\PhpParser\NodeTraverser::STOP_TRAVERSAL));
        $visitor->expects($this->at(8))->method('afterTraverse');
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $this->assertEquals([$printNode], $traverser->traverse($stmts));
    }
    public function testRemovingVisitor()
    {
        $visitor1 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor2 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor3 = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor1);
        $traverser->addVisitor($visitor2);
        $traverser->addVisitor($visitor3);
        $preExpected = array($visitor1, $visitor2, $visitor3);
        $this->assertAttributeSame($preExpected, 'visitors', $traverser, 'The appropriate visitors have not been added');
        $traverser->removeVisitor($visitor2);
        $postExpected = array(0 => $visitor1, 2 => $visitor3);
        $this->assertAttributeSame($postExpected, 'visitors', $traverser, 'The appropriate visitors are not present after removal');
    }
    public function testNoCloneNodes()
    {
        $stmts = array(new \MolliePrefix\PhpParser\Node\Stmt\Echo_(array(new \MolliePrefix\PhpParser\Node\Scalar\String_('Foo'), new \MolliePrefix\PhpParser\Node\Scalar\String_('Bar'))));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $this->assertSame($stmts, $traverser->traverse($stmts));
    }
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage leaveNode() may only return an array if the parent structure is an array
     */
    public function testReplaceByArrayOnlyAllowedIfParentIsArray()
    {
        $stmts = array(new \MolliePrefix\PhpParser\Node\Expr\UnaryMinus(new \MolliePrefix\PhpParser\Node\Scalar\LNumber(42)));
        $visitor = $this->getMockBuilder('MolliePrefix\\PhpParser\\NodeVisitor')->getMock();
        $visitor->method('leaveNode')->willReturn(array(new \MolliePrefix\PhpParser\Node\Scalar\DNumber(42.0)));
        $traverser = new \MolliePrefix\PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);
    }
}
