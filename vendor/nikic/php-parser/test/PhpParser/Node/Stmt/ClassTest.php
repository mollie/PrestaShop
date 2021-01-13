<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

class ClassTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    public function testIsAbstract()
    {
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo', array('type' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT));
        $this->assertTrue($class->isAbstract());
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo');
        $this->assertFalse($class->isAbstract());
    }
    public function testIsFinal()
    {
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo', array('type' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_FINAL));
        $this->assertTrue($class->isFinal());
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo');
        $this->assertFalse($class->isFinal());
    }
    public function testGetMethods()
    {
        $methods = array(new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('foo'), new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('bar'), new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('fooBar'));
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo', array('stmts' => array(new \MolliePrefix\PhpParser\Node\Stmt\TraitUse(array()), $methods[0], new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array()), $methods[1], new \MolliePrefix\PhpParser\Node\Stmt\Property(0, array()), $methods[2])));
        $this->assertSame($methods, $class->getMethods());
    }
    public function testGetMethod()
    {
        $methodConstruct = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('__CONSTRUCT');
        $methodTest = new \MolliePrefix\PhpParser\Node\Stmt\ClassMethod('test');
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo', array('stmts' => array(new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array()), $methodConstruct, new \MolliePrefix\PhpParser\Node\Stmt\Property(0, array()), $methodTest)));
        $this->assertSame($methodConstruct, $class->getMethod('__construct'));
        $this->assertSame($methodTest, $class->getMethod('test'));
        $this->assertNull($class->getMethod('nonExisting'));
    }
    public function testDeprecatedTypeNode()
    {
        $class = new \MolliePrefix\PhpParser\Node\Stmt\Class_('Foo', array('type' => \MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT));
        $this->assertTrue($class->isAbstract());
        $this->assertSame(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT, $class->flags);
        $this->assertSame(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT, $class->type);
    }
}
