<?php

namespace MolliePrefix;

/**
 * @coversDefaultClass \Foo\CoveredClass
 */
class NamespaceCoverageCoversClassTest extends \MolliePrefix\PHPUnit_Framework_TestCase
{
    /**
     * @covers ::privateMethod
     * @covers ::protectedMethod
     * @covers ::publicMethod
     * @covers \Foo\CoveredParentClass::privateMethod
     * @covers \Foo\CoveredParentClass::protectedMethod
     * @covers \Foo\CoveredParentClass::publicMethod
     */
    public function testSomething()
    {
        $o = new \MolliePrefix\Foo\CoveredClass();
        $o->publicMethod();
    }
}
/**
 * @coversDefaultClass \Foo\CoveredClass
 */
\class_alias('MolliePrefix\\NamespaceCoverageCoversClassTest', 'MolliePrefix\\NamespaceCoverageCoversClassTest', \false);
