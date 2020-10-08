<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

class ClassConstTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideModifiers
     */
    public function testModifiers($modifier)
    {
        $node = new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(
            array(),
            // invalid
            \constant('PhpParser\\Node\\Stmt\\Class_::MODIFIER_' . \strtoupper($modifier))
        );
        $this->assertTrue($node->{'is' . $modifier}());
    }
    public function testNoModifiers()
    {
        $node = new \MolliePrefix\PhpParser\Node\Stmt\ClassConst(array(), 0);
        $this->assertTrue($node->isPublic());
        $this->assertFalse($node->isProtected());
        $this->assertFalse($node->isPrivate());
        $this->assertFalse($node->isStatic());
    }
    public function provideModifiers()
    {
        return array(array('public'), array('protected'), array('private'));
    }
}
