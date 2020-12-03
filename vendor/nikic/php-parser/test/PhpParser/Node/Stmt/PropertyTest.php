<?php

namespace MolliePrefix\PhpParser\Node\Stmt;

class PropertyTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideModifiers
     */
    public function testModifiers($modifier)
    {
        $node = new \MolliePrefix\PhpParser\Node\Stmt\Property(\constant('PhpParser\\Node\\Stmt\\Class_::MODIFIER_' . \strtoupper($modifier)), array());
        $this->assertTrue($node->{'is' . $modifier}());
    }
    public function testNoModifiers()
    {
        $node = new \MolliePrefix\PhpParser\Node\Stmt\Property(0, array());
        $this->assertTrue($node->isPublic());
        $this->assertFalse($node->isProtected());
        $this->assertFalse($node->isPrivate());
        $this->assertFalse($node->isStatic());
    }
    public function testStaticImplicitlyPublic()
    {
        $node = new \MolliePrefix\PhpParser\Node\Stmt\Property(\MolliePrefix\PhpParser\Node\Stmt\Class_::MODIFIER_STATIC, array());
        $this->assertTrue($node->isPublic());
        $this->assertFalse($node->isProtected());
        $this->assertFalse($node->isPrivate());
        $this->assertTrue($node->isStatic());
    }
    public function provideModifiers()
    {
        return array(array('public'), array('protected'), array('private'), array('static'));
    }
}
