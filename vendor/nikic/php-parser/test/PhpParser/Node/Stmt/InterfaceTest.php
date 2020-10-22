<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

use MolliePrefix\PhpParser\Node;
class InterfaceTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testGetMethods()
    {
        $methods = array(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('foo'), new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('bar'));
        $interface = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo', array('stmts' => array(new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('C1', new \MolliePrefix\PhpParser\Node\Scalar\String_('C1')))), $methods[0], new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('C2', new \MolliePrefix\PhpParser\Node\Scalar\String_('C2')))), $methods[1], new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(new \MolliePrefix\PhpParser\Node\Const_('C3', new \MolliePrefix\PhpParser\Node\Scalar\String_('C3')))))));
        $this->assertSame($methods, $interface->getMethods());
    }
}
